<?php
// includes/db.php - Database Connection

define('DB_HOST', 'sql300.byetcluster.com');
define('DB_USER', 'if0_41952532');
define('DB_PASS', '7XkOCoDxDYsli');
define('DB_NAME', 'if0_41952532_olis_coffee');

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}

function getDB() {
    return Database::getInstance()->getConnection();
}
?>