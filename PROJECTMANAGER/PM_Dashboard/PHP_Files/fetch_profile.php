<?php
session_start();
require_once __DIR__ . '/../../../database.php';
header('Content-Type: application/json');

class ProfileFetcher {
    private $conn;
    private $user_id;

    public function __construct($conn, $user_id) {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    public function getProfile() {
        $stmt = $this->conn->prepare("
            SELECT 
                u.name, 
                COALESCE(d.job_title, '') AS job_title, 
                COALESCE(d.status, '') AS status, 
                COALESCE(d.profile_pic, '') AS profile_pic
            FROM users u
            LEFT JOIN user_details d ON u.id = d.user_id
            WHERE u.id = ?
        ");
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = null;
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
        }

        $stmt->close();
        return $data;
    }
}

// 🔒 Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    $user_id = $_SESSION['user_id'];
    $profileFetcher = new ProfileFetcher($conn, $user_id);
    $data = $profileFetcher->getProfile();

    if ($data) {
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No profile found.']);
    }

    $conn->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
