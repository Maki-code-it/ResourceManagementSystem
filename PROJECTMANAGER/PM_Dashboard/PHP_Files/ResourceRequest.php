<?php
class ResourceRequestHandler {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function submitRequest($data) {
        $query = "INSERT INTO resource_requests 
        (project_name, resource_type, num_resources, skills, start_date, duration, priority, notes, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";


        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return ["status" => "error", "message" => "Prepare failed: " . $this->conn->error];
        }

        $status = 'pending';
        $stmt->bind_param(
            "ssissssss",
            $data['projectName'],
            $data['resourceType'],
            $data['numResources'],
            $data['skills'],
            $data['startDate'],
            $data['duration'],
            $data['priority'],
            $data['notes'],
            $status
        );

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Resource request submitted successfully."];
        } else {
            return ["status" => "error", "message" => "Execution failed: " . $stmt->error];
        }
    }
}
?>
