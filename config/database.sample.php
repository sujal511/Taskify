<?php
/**
 * Database Configuration Sample
 * Copy this file to database.php and update with your credentials
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'progress_tracker');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            // Log the error for debugging
            error_log("Database connection error: " . $exception->getMessage());
            
            // Display user-friendly error message
            if (defined('APP_ENV') && APP_ENV === 'development') {
                echo "Connection error: " . $exception->getMessage();
            } else {
                echo "Database connection failed. Please try again later.";
            }
            
            return null;
        }
        
        return $this->conn;
    }
}
?>