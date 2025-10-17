<?php
require_once "../PHP_Files/login.php"; // adjust path as needed

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = new User();
    $result = $user->login($email, $password);

    // If login successful, assign a redirect path based on role
    if ($result['success']) {
        switch (strtolower($result['role'])) {
            case 'project_manager':
                $result['redirect'] = '../../PROJECTMANAGER/PM_Dashboard/HTML_Files/PM_dashboard.html';
                break;
            case 'employee':
                $result['redirect'] = '../EMPLOYEE/Employee_Dashboard/HTML_Files/employee_dashboard.html';
                break;
            case 'admin':
                $result['redirect'] = '../ADMIN/Admin_Dashboard/HTML_Files/admin_dashboard.html';
                break;
            default:
                $result['redirect'] = '../HTML_Files/default_dashboard.html';
        }
    }

    echo json_encode($result);
}
?>
