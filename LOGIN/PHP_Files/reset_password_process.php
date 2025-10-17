<?php
require_once "../PHP_Files/login.php"; // adjust according to your folder structure

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['password'] ?? '';

    $user = new User();
    $result = $user->resetPassword($token, $newPassword);

    echo json_encode($result);
}
?>
