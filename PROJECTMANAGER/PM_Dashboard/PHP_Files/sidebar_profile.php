<?php
session_start();
require_once __DIR__ . '/../../../database.php'; // adjust path if needed

// ✅ Create a Database instance
$db = new Database();
$conn = $db->connect(); // now you have a working connection

// Get logged-in user ID (for now, use 1 if not logged in)
$user_id = $_SESSION['user_id'] ?? 1;

// Fetch user info + profile picture
$sql = "SELECT u.name, u.email, d.profile_pic 
        FROM users AS u
        LEFT JOIN user_details AS d ON u.id = d.user_id
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$name = htmlspecialchars($user['name'] ?? 'Unknown');
$email = htmlspecialchars($user['email'] ?? 'N/A');

// Use uploaded profile picture if available, else default
$profilePic = !empty($user['profile_pic'])
    ? "../../../" . $user['profile_pic']
    : "../../../assets/default_profile.png";

?>
