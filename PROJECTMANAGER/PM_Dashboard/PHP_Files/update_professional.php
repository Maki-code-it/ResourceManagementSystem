<?php
session_start();
require_once __DIR__ . '/../../../database.php';
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class UserProfile {
    private $conn;
    private $user_id;

    public function __construct($conn, $user_id) {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    public function updateProfessionalInfo($email, $phone, $location, $department, $employee_id, $join_date) {
        try {
            // Update email in users table
            if (!empty($email)) {
                $stmt = $this->conn->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->bind_param("si", $email, $this->user_id);
                if (!$stmt->execute()) throw new Exception($stmt->error);
                $stmt->close();
            }

            // Check if record exists
            $stmt = $this->conn->prepare("SELECT id FROM user_details WHERE user_id = ?");
            $stmt->bind_param("i", $this->user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->num_rows > 0;
            $stmt->close();

            // Prepare values: convert empty strings to null
           // Fetch existing details first
            $stmt = $this->conn->prepare("SELECT phone_number, location, department, employee_id, join_date FROM user_details WHERE user_id=?");
            $stmt->bind_param("i", $this->user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            $stmt->close();

            // Use new value if provided, else keep old value
            $phone = !empty($phone) ? $phone : ($existing['phone_number'] ?? null);
            $location = !empty($location) ? $location : ($existing['location'] ?? null);
            $department = !empty($department) ? $department : ($existing['department'] ?? null);
            $employee_id = !empty($employee_id) ? $employee_id : ($existing['employee_id'] ?? null);
            $join_date = !empty($join_date) ? $join_date : ($existing['join_date'] ?? null);


            if ($exists) {
                $stmt = $this->conn->prepare("
                    UPDATE user_details 
                    SET phone_number=?, location=?, department=?, employee_id=?, join_date=? 
                    WHERE user_id=?
                ");

                // If join_date is null, we need to use NULL in MySQL
                if ($join_date === null) {
                    $stmt->bind_param("sssssi", $phone, $location, $department, $employee_id, $join_date, $this->user_id);
                    $stmt->send_long_data(4, null); // ensures MySQL NULL
                } else {
                    $stmt->bind_param("sssssi", $phone, $location, $department, $employee_id, $join_date, $this->user_id);
                }
            } else {
                $stmt = $this->conn->prepare("
                    INSERT INTO user_details (user_id, phone_number, location, department, employee_id, join_date)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                if ($join_date === null) {
                    $stmt->bind_param("isssss", $this->user_id, $phone, $location, $department, $employee_id, $join_date);
                    $stmt->send_long_data(4, null); // ensures MySQL NULL
                } else {
                    $stmt->bind_param("isssss", $this->user_id, $phone, $location, $department, $employee_id, $join_date);
                }
            }

            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();

            return ['status' => 'success', 'message' => 'Professional information updated successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$db = new Database();
$conn = $db->connect();
$user_id = $_SESSION['user_id'];

$profile = new UserProfile($conn, $user_id);

$response = $profile->updateProfessionalInfo(
    $_POST['email'] ?? '',
    $_POST['phone'] ?? '',
    $_POST['location'] ?? '',
    $_POST['department'] ?? '',
    $_POST['employee_id'] ?? '',
    $_POST['join_date'] ?? ''
);

echo json_encode($response);
$conn->close();
