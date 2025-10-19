<?php
session_start();

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt'); // path to your log file
ini_set('display_errors', 0); // hide errors from browser
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../../../database.php';

class SkillManager {
    private $conn;
    private $user_id;

    public function __construct($user_id) {
        $this->user_id = $user_id;
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function addSkill($skill) {
        $skill = trim($skill);
        if ($skill === '') {
            return ['status'=>'error','message'=>'Skill cannot be empty'];
        }

        try {
            // Check if skill already exists
            $stmt = $this->conn->prepare("SELECT id FROM user_skills WHERE user_id=? AND skill_name=?");
            $stmt->bind_param("is", $this->user_id, $skill);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $stmt->close();
                return ['status'=>'error','message'=>'Skill already added'];
            }
            $stmt->close();

            // Insert new skill
            $stmt = $this->conn->prepare("INSERT INTO user_skills (user_id, skill_name) VALUES (?, ?)");
            $stmt->bind_param("is", $this->user_id, $skill);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();

            return ['status'=>'success','message'=>'Skill added successfully'];

        } catch (Exception $e) {
            error_log($e->getMessage());
            return ['status'=>'error','message'=>'Failed to add skill'];
        }
    }

    public function __destruct() {
        $this->conn->close();
    }
}

// Validate session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'User not logged in']);
    exit;
}

// Instantiate and process
$manager = new SkillManager($_SESSION['user_id']);
$response = $manager->addSkill($_POST['skill'] ?? '');
echo json_encode($response);
