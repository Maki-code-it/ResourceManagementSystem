    <?php
session_start();

// Database connection
$host = "localhost";
$dbname = "resource_management";
$username = "root";
$password = "0714";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch the logged-in manager's information
$manager_query = "SELECT id, name, email, role FROM users WHERE role = 'project_manager' LIMIT 1";
$manager_stmt = $conn->prepare($manager_query);
$manager_stmt->execute();
$manager = $manager_stmt->fetch(PDO::FETCH_ASSOC);

if (!$manager) {
    die("No project manager found in the database.");
}

// Set manager_id from the fetched manager
$manager_id = $manager['id'];
$manager_name = $manager['name'];

// Fetch all projects for this manager
$query = "SELECT pr.*, 
          COUNT(DISTINCT pa.employee_id) as assigned_employees,
          CASE 
              WHEN pr.status = 'approved' THEN 'Active'
              WHEN pr.status = 'pending' THEN 'In Progress'
              WHEN pr.status = 'rejected' THEN 'Rejected'
          END as display_status
          FROM project_requests pr
          LEFT JOIN project_assignments pa ON pr.id = pa.request_id
          WHERE pr.manager_id = :manager_id
          GROUP BY pr.id
          ORDER BY pr.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bindParam(':manager_id', $manager_id);
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to calculate progress (based on duration and creation date)
function calculateProgress($created_at, $duration, $status) {
    if ($status === 'rejected') return 0;
    
    $start = strtotime($created_at);
    $now = time();
    $total_duration = $duration * 24 * 60 * 60; // Convert days to seconds
    
    $elapsed = $now - $start;
    $progress = ($elapsed / $total_duration) * 100;
    
    return min(100, max(0, round($progress)));
}

// Function to calculate deadline
function getDeadline($created_at, $duration) {
    return date('M d, Y', strtotime($created_at . " + $duration days"));
}

// Function to get assigned team members
function getTeamMembers($conn, $request_id) {
    $query = "SELECT u.name FROM users u
              INNER JOIN project_assignments pa ON u.id = pa.employee_id
              WHERE pa.request_id = :request_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':request_id', $request_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get manager initials for display
$manager_initials = strtoupper(substr($manager_name, 0, 1));
if (strpos($manager_name, ' ') !== false) {
    $name_parts = explode(' ', $manager_name);
    $manager_initials = strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects - Project Manager</title>
    <link rel="stylesheet" href="../../CSS_Files/styles.css">
    <script>
        function redirectToNewProject() {
            window.location.href = 'PM_RequestProject.html';
        }

        function comingSoonAlert() {
            alert('Coming Soon! This feature is under development.');
        }

        function searchProjects() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const cards = document.getElementsByClassName('project-card');

            for (let i = 0; i < cards.length; i++) {
                const title = cards[i].querySelector('h3').textContent.toLowerCase();
                if (title.includes(filter)) {
                    cards[i].style.display = '';
                } else {
                    cards[i].style.display = 'none';
                }
            }
        }
    </script>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="profile-circle"><?php echo $manager_initials; ?></div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($manager_name); ?></h2>
                <p><?php echo htmlspecialchars($manager['email']); ?></p>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="PM_dashboard.php" class="nav-item">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="PM_RequestProject.php" class="nav-item">
                <span class="nav-icon">📝</span>
                <span>Request Resources</span>
            </a>
            <a href="PM_MyProjects.php" class="nav-item active">
                <span class="nav-icon">📁</span>
                <span>My Projects</span>
            </a>
            <a href="PM_Profile.php" class="nav-item">
                <span class="nav-icon">👤</span>
                <span>Profile</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">⚙️</span>
                <span>Settings</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div>
                <h1>My Projects</h1>
                <p class="subtitle">Manage and track all your projects</p>
            </div>
            <div class="header-actions">
                <button class="notification-btn">
                    🔔
                    <span class="notification-badge"></span>
                </button>
                <div class="profile-circle"><?php echo $manager_initials; ?></div>
            </div>
        </header>

        <!-- Projects Content -->
        <div class="dashboard-container">
            <!-- Search and New Project -->
            <div class="projects-header">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" placeholder="Search projects..." onkeyup="searchProjects()">
                </div>
                <button class="btn-primary" onclick="redirectToNewProject()">
                    <span>➕</span> New Project
                </button>
            </div>

            <!-- Projects List -->
            <div class="projects-list-view">
                <?php if (empty($projects)): ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <p>No projects found. Click "New Project" to create your first project.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($projects as $project): 
                        $progress = calculateProgress($project['created_at'], $project['duration'], $project['status']);
                        $deadline = getDeadline($project['created_at'], $project['duration']);
                        $team_members = getTeamMembers($conn, $project['id']);
                        
                        // Determine badge class
                        $badge_class = 'badge-active';
                        if ($project['display_status'] === 'In Progress') {
                            $badge_class = 'badge-inprogress';
                        } elseif ($project['display_status'] === 'Rejected') {
                            $badge_class = 'badge-rejected';
                        }
                    ?>
                    <div class="project-card">
                        <div class="project-card-header">
                            <div class="project-card-title">
                                <h3><?php echo htmlspecialchars($project['project_name']); ?></h3>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($project['display_status']); ?>
                                </span>
                            </div>
                            <div class="project-actions">
                                <button class="icon-btn" onclick="comingSoonAlert()" title="Edit">✏️</button>
                                <button class="icon-btn" onclick="comingSoonAlert()" title="Delete">🗑️</button>
                            </div>
                        </div>
                        
                        <div class="project-card-body">
                            <div class="project-detail">
                                <p class="detail-label">Progress</p>
                                <div class="progress-wrapper">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                    <span class="progress-text"><?php echo $progress; ?>%</span>
                                </div>
                            </div>
                            
                            <div class="project-detail">
                                <p class="detail-label">Deadline</p>
                                <p class="detail-value">
                                    <span class="icon">🕐</span>
                                    <?php echo $deadline; ?>
                                </p>
                            </div>
                            
                            <div class="project-detail">
                                <p class="detail-label">Team (<?php echo $project['assigned_employees']; ?>/<?php echo $project['employees_needed']; ?>)</p>
                                <div class="team-avatars">
                                    <?php if (empty($team_members)): ?>
                                        <span style="font-size: 12px; color: #666;">No team members assigned yet</span>
                                    <?php else: ?>
                                        <?php foreach ($team_members as $member): 
                                            $initial = strtoupper(substr($member['name'], 0, 1));
                                        ?>
                                            <div class="avatar" title="<?php echo htmlspecialchars($member['name']); ?>">
                                                <?php echo $initial; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="project-detail">
                                <p class="detail-label">Required Skills</p>
                                <p class="detail-value" style="font-size: 13px;">
                                    <?php echo htmlspecialchars($project['required_skills']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</body>
</html>
