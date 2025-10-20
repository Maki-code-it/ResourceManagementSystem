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
        $query = "SELECT 
                  id_requests as id,
                  project_name,
                  resource_type,
                  num_resources as employees_needed,
                  skills as required_skills,
                  start_date,
                  duration,
                  priority,
                  notes,
                  status,
                  created_at,
                  0 as assigned_employees,
                  CASE 
                      WHEN status = 'approved' THEN 'Active'
                      WHEN status = 'pending' THEN 'In Progress'
                      WHEN status = 'rejected' THEN 'Rejected'
                  END as display_status
                  FROM resource_requests
                  ORDER BY created_at DESC";

        $result = $this->conn->query($query);
        
        if (!$result) {
            throw new Exception("Failed to execute query: " . $this->conn->error);
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getTeamMembers($request_id) {
        return [];
    }

    public function calculateProgress($created_at, $duration, $status) {
        if ($status === 'rejected') {
            return 0;
        }
        
        $start = strtotime($created_at);
        $now = time();
        
        $duration_days = 30;
        if (preg_match('/(\d+)\s*month/i', $duration, $matches)) {
            $duration_days = intval($matches[1]) * 30;
        } elseif (preg_match('/(\d+)\s*week/i', $duration, $matches)) {
            $duration_days = intval($matches[1]) * 7;
        } elseif (preg_match('/(\d+)\s*day/i', $duration, $matches)) {
            $duration_days = intval($matches[1]);
        }
        
        $total_duration = $duration_days * 24 * 60 * 60;
        $elapsed = $now - $start;
        $progress = ($elapsed / $total_duration) * 100;
        
        return min(100, max(0, round($progress)));
    }

    public function getDeadline($created_at, $duration) {
        $duration_days = 30;
        if (preg_match('/(\d+)\s*month/i', $duration, $matches)) {
            $duration_days = intval($matches[1]) * 30;
        } elseif (preg_match('/(\d+)\s*week/i', $duration, $matches)) {
            $duration_days = intval($matches[1]) * 7;
        } elseif (preg_match('/(\d+)\s*day/i', $duration, $matches)) {
            $duration_days = intval($matches[1]);
        }
        
        return date('M d, Y', strtotime($created_at . " + $duration_days days"));
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

    public function deleteProject($project_id) {
        $query = "DELETE FROM resource_requests WHERE id_requests = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $project_id);
        
        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Project deleted successfully"];
        } else {
            throw new Exception("Failed to delete project: " . $stmt->error);
        }
    }

    public function getProjectById($project_id) {
        $query = "SELECT * FROM resource_requests WHERE id_requests = ?";
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    public function updateProject($project_id, $data) {
        $query = "UPDATE resource_requests 
                  SET project_name = ?, 
                      resource_type = ?, 
                      num_resources = ?, 
                      skills = ?, 
                      start_date = ?, 
                      duration = ?, 
                      priority = ?, 
                      notes = ?
                  WHERE id_requests = ?";
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param(
            "ssisssssi",
            $data['projectName'],
            $data['resourceType'],
            $data['numResources'],
            $data['skills'],
            $data['startDate'],
            $data['duration'],
            $data['priority'],
            $data['notes'],
            $project_id
        );

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Project updated successfully"];
        } else {
            throw new Exception("Failed to update project: " . $stmt->error);
        }
    }
}
?>