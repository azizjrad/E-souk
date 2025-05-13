<?php
class Database {
    private static $instance = null;
    private $conn; // Changed from $connection to $conn to match usage

    private function __construct() {
        try {
            $hostname = 'localhost';
            $username = 'aziz'; // Your username
            $password = 'jradz123';     // Your password
            $database = 'bd-esouk-2'; // Your database name
            
            $this->conn = new PDO(
                "mysql:host=$hostname;dbname=$database;charset=utf8mb4", 
                $username, 
                $password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => false
                )
            );
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->conn; // Changed from ->connection to ->conn
    }
}

?>