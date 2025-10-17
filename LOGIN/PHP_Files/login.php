<?php
require_once "../../database.php"; // or absolute path

class User extends Database {
    public function login($email, $password) {
        $conn = $this->connect();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                return ["success" => true, "role" => $user['role']];
            }
        }
        return ["success" => false];
    }

    public function createResetToken($email) {
        $conn = $this->connect();

        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ["success" => false];
        }

        // Generate token and expiry
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save to DB
        $update = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        return ["success" => true, "token" => $token];
    }

    public function resetPassword($token, $newPassword) {
        $conn = $this->connect();
    
        // Hash the new password here
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
    
        // Validate token
        $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token = ? AND token_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows === 0) {
            return ["success" => false, "message" => "Invalid or expired token"];
        }
    
        $user = $result->fetch_assoc();
        $email = $user['email'];
    
        // Save hashed password to DB
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?");
        $update->bind_param("ss", $hashed, $email);
        $update->execute();
    
        return ["success" => true];
    }
    

    
}
?>
