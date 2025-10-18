<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
header("Content-Type: application/json");

// Try to include files with better error handling
// Go up 3 levels: PHP_Files -> PM_Dashboard -> PROJECTMANAGER -> ResourceManagementSystem
$database_path = __DIR__ . '/../../../database.php';
$manager_path = __DIR__ . '/ProjectManager.php';

if (!file_exists($database_path)) {
    echo json_encode(['status' => 'error', 'message' => 'database.php not found at: ' . $database_path]);
    exit;
}

if (!file_exists($manager_path)) {
    echo json_encode(['status' => 'error', 'message' => 'ProjectManager.php not found at: ' . $manager_path]);
    exit;
}

require_once $database_path;
require_once $manager_path;

try {
    $database = new Database();
    $db = $database->connect();
    
    $projectManager = new ProjectManager($db);
    
    $manager_data = $projectManager->getManagerData();
    $manager_initials = $projectManager->getManagerInitials();
    $projects = $projectManager->getAllProjects();
    
    // Process projects data
    $processed_projects = [];
    foreach ($projects as $project) {
        $progress = $projectManager->calculateProgress(
            $project['created_at'], 
            $project['duration'], 
            $project['status']
        );
        $deadline = $projectManager->getDeadline($project['created_at'], $project['duration']);
        $team_members = $projectManager->getTeamMembers($project['id']);
        $badge_class = $projectManager->getBadgeClass($project['display_status']);
        
        $processed_projects[] = [
            'id' => $project['id'],
            'project_name' => $project['project_name'],
            'display_status' => $project['display_status'],
            'badge_class' => $badge_class,
            'progress' => $progress,
            'deadline' => $deadline,
            'assigned_employees' => $project['assigned_employees'],
            'employees_needed' => $project['employees_needed'],
            'required_skills' => $project['required_skills'],
            'team_members' => $team_members
        ];
    }
    
    // Return JSON response
    echo json_encode([
        'status' => 'success',
        'manager' => [
            'id' => $manager_data['id'],
            'name' => $manager_data['name'],
            'email' => $manager_data['email'],
            'initials' => $manager_initials
        ],
        'projects' => $processed_projects
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>s