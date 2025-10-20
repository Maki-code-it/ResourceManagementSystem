<?php
require_once __DIR__ . '../../PM_Dashboard/PHP_Files/sidebar_profile.php';

// Load the HTML file
$html = file_get_contents(__DIR__ . '/../HTML_Files/PM_RequestProject.html');
$html = file_get_contents(__DIR__ . '/../HTML_Files/PM_dashboard.html');
$html = file_get_contents(__DIR__ . '/../HTML_Files/PM_MyProjects.html');
$html = file_get_contents(__DIR__ . '/../HTML_Files/PM_Profile.html');

// Replace placeholders with real data
$html = str_replace(
    ['{{name}}', '{{email}}', '{{profilePic}}'],
    [$name, $email, $profilePic],
    $html
);

// Output the final HTML page
echo $html;
?>
