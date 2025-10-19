<?php
session_start();
require_once __DIR__ . '/../../../database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$db = new Database();
$conn = $db->connect();
$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT skill_name FROM user_skills WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $skills = [];
    while ($row = $result->fetch_assoc()) {
        $skills[] = $row['skill_name']; // <- use the correct column name
    }

    echo json_encode(['status' => 'success', 'skills' => $skills]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
