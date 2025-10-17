<?php
require_once "../PHP_Files/login.php"; // adjust according to your folder structure

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';       // changed to $email
    $password = $_POST['password'] ?? '';

    $user = new User();                   // create User object
    $result = $user->login($email, $password);  // pass correct variable

    echo json_encode($result);            // return JSON response
}
?>
