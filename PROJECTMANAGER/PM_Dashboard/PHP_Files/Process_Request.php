<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

require_once __DIR__ . '/../../../database.php';
require_once "ResourceRequest.php";

session_start(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "User not authenticated"]);
        exit;
    }

    $database = new Database();
    $db = $database->connect();

    $handler = new ResourceRequestHandler($db);

    $data = [
        "projectName" => $_POST['projectName'] ?? '',
        "resourceType" => $_POST['resourceType'] ?? '',
        "numResources" => $_POST['numResources'] ?? '',
        "skills" => $_POST['skills'] ?? '',
        "startDate" => $_POST['startDate'] ?? '',
        "duration" => $_POST['duration'] ?? '',
        "priority" => $_POST['priority'] ?? '',
        "notes" => $_POST['notes'] ?? ''
    ];

    $userId = $_SESSION['user_id']; 

    // Pass both $data and $userId
    $response = $handler->submitRequest($data, $userId);
    echo json_encode($response);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
