<?php
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../PHP_Files/login.php';
require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    $user = new User();
    $result = $user->createResetToken($email);

    if ($result['success']) {
        $resetLink = "http://localhost/LOGIN/HTML_Files/reset_password.html?token=" . urlencode($result['token']);

        // Send Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'plppasig2@gmail.com'; 
            $mail->Password = 'lgxf itnz cklv endq';   // use App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('plppasig2@gmail.com', 'Resource Management');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Click <a href='$resetLink'>here</a> to reset your password. This link expires in 1 hour.";

            $mail->send();
            echo json_encode(["success" => true]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "error" => $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(["success" => false]);
    }
}
?>
