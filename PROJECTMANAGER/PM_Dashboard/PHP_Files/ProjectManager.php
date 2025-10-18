<?php
class ProjectManager {
    private $conn;
    private $manager_id;
    private $manager_data;

    public function __construct($db) {
        $this->conn = $db;
        $this->loadManager();
    }

    private function loadManager() {
        $query = "SELECT id, name, email, role FROM users WHERE role = 'project_manager' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $this->manager_data = $result->fetch_assoc();

        if (!$this->manager_data) {
            throw new Exception("No project manager found in the database.");
        }

        $this->manager_id = $this->manager_data['id'];
    }

    public function getManagerData() {
        return $this->manager_data;
    }

    public function getManagerId() {
        return $this->manager_id;
    }

    public function getManagerInitials() {
        $name = $this->manager_data['name'];
        $initials = strtoupper(substr($name, 0, 1));
        
        if (strpos($name, ' ') !== false) {
            $name_parts = explode(' ', $name);
            $initials = strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
        }
        
        return $initials;
    }

    public function getAllProjects() {
        $query = "SELECT pr.*, 
                  COUNT(DISTINCT pa.employee_id) as assigned_employees,
                  CASE 
                      WHEN pr.status = 'approved' THEN 'Active'
                      WHEN pr.status = 'pending' THEN 'In Progress'
                      WHEN pr.status = 'rejected' THEN 'Rejected'
                  END as display_status
                  FROM project_requests pr
                  LEFT JOIN project_assignments pa ON pr.id = pa.request_id
                  WHERE pr.manager_id = ?
                  GROUP BY pr.id
                  ORDER BY pr.created_at DESC";

        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $this->manager_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getTeamMembers($request_id) {
        $query = "SELECT u.name FROM users u
                  INNER JOIN project_assignments pa ON u.id = pa.employee_id
                  WHERE pa.request_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function calculateProgress($created_at, $duration, $status) {
        if ($status === 'rejected') {
            return 0;
        }
        
        $start = strtotime($created_at);
        $now = time();
        $total_duration = $duration * 24 * 60 * 60;
        
        $elapsed = $now - $start;
        $progress = ($elapsed / $total_duration) * 100;
        
        return min(100, max(0, round($progress)));
    }

    public function getDeadline($created_at, $duration) {
        return date('M d, Y', strtotime($created_at . " + $duration days"));
    }

    public function getBadgeClass($display_status) {
        $badge_class = 'badge-active';
        
        if ($display_status === 'In Progress') {
            $badge_class = 'badge-inprogress';
        } elseif ($display_status === 'Rejected') {
            $badge_class = 'badge-rejected';
        }
        
        return $badge_class;
    }
}
?>