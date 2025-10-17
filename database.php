<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $dbname = "resource_management";

    protected function connect() {
        $conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        if ($conn->connect_error) {
            die("Database Connection Failed: " . $conn->connect_error);
        }
        return $conn;
    }
}
?>
