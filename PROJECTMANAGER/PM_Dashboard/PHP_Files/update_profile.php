<?php
session_start();
require_once __DIR__ . '/../../../database.php';
header('Content-Type: application/json');

class ProfileUpdater {
    private $conn;
    private $user_id;
    // Absolute path to uploads folder outside PROJECTMANAGER
    private $upload_dir;

    public function __construct($conn, $user_id) {
        $this->conn = $conn;
        $this->user_id = $user_id;
       // Absolute path to uploads folder
        $this->upload_dir = realpath(__DIR__ . '/../../../uploads/profile_pictures/') . '/';// adjust path to reach ResourceManagementSystem/uploads
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }

    }

    public function updateProfile($name, $title, $status, $file) {
        $this->updateUserName($name);
        $profile_pic = $this->handleProfilePicture($file);

        return $this->userDetailsExists()
            ? $this->updateUserDetails($title, $status, $profile_pic)
            : $this->insertUserDetails($title, $status, $profile_pic);
    }

    private function updateUserName($name) {
        $stmt = $this->conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $this->user_id);
        $stmt->execute();
        $stmt->close();
    }

    private function handleProfilePicture($file) {
        if (!isset($file['profile_pic']) || $file['profile_pic']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file_tmp = $file['profile_pic']['tmp_name'];
        $file_name = uniqid("profile_") . "_" . preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($file['profile_pic']['name']));
        $file_path = $this->upload_dir . $file_name;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_type = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("Invalid file type.");
        }

        if (!move_uploaded_file($file_tmp, $file_path)) {
            throw new Exception("Image upload failed. Tried path: $file_path" );
        }

        // Store relative path for DB
        return "uploads/profile_pictures/" . $file_name;
    }

    private function userDetailsExists() {
        $stmt = $this->conn->prepare("SELECT id FROM user_details WHERE user_id = ?");
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    private function updateUserDetails($title, $status, $profile_pic = null) {
        if ($profile_pic) {
            $stmt = $this->conn->prepare("UPDATE user_details SET job_title = ?, status = ?, profile_pic = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $title, $status, $profile_pic, $this->user_id);
        } else {
            $stmt = $this->conn->prepare("UPDATE user_details SET job_title = ?, status = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $title, $status, $this->user_id);
        }
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    private function insertUserDetails($title, $status, $profile_pic = null) {
        if ($profile_pic) {
            $stmt = $this->conn->prepare("INSERT INTO user_details (user_id, job_title, status, profile_pic) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $this->user_id, $title, $status, $profile_pic);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO user_details (user_id, job_title, status) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $this->user_id, $title, $status);
        }
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}

// Validate session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

// DB connection
$db = new Database();
$conn = $db->connect();

// Collect form data
$user_id = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$title = trim($_POST['job_title'] ?? '');
$status = trim($_POST['status'] ?? '');

try {
    $updater = new ProfileUpdater($conn, $user_id);
    $success = $updater->updateProfile($name, $title, $status, $_FILES);

    echo json_encode([
        'status' => $success ? 'success' : 'error',
        'message' => $success ? 'Profile updated successfully.' : 'Failed to update profile.'
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
