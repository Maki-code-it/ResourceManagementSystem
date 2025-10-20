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
    // Get email from users table
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $email = $result['email'] ?? '';

    // Get professional info from user_details
    $stmt = $conn->prepare("
        SELECT phone_number, location, department, employee_id, join_date
        FROM user_details
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $details = $stmt->get_result()->fetch_assoc() ?? [];

    $response = [
        'status' => 'success',
        'data' => array_merge(['email' => $email], $details)
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
