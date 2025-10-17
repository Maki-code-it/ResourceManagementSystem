<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

require_once __DIR__ . '/../../../database.php';
require_once "ResourceRequest.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    $response = $handler->submitRequest($data);
    echo json_encode($response);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
