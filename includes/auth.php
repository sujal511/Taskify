<?php
/**
 * Secure Authentication Functions
 */

// Include config if not already included
if (!defined('APP_ENV')) {
    require_once __DIR__ . '/../config/config.php';
}

require_once __DIR__ . '/../config/database.php';

/**
 * Start secure session with proper configuration
 */
function startSecureSession() {
    if (session_status() == PHP_SESSION_NONE) {
        // Basic session configuration for XAMPP compatibility
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        
        // Only set secure cookies if HTTPS is available
        if (isHTTPS()) {
            ini_set('session.cookie_secure', 1);
        }
        
        session_start();
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

/**
 * Check if connection is HTTPS
 */
function isHTTPS() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
        || $_SERVER['SERVER_PORT'] == 443
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

/**
 * Register a new user
 */
function registerUser($username, $password, $confirmPassword) {
    // Validation
    $errors = [];
    
    if (empty($username) || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long.';
    }
    
    if (strlen($username) > 50) {
        $errors[] = 'Username must be less than 50 characters.';
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }
    
    if (empty($password) || strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number.';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if database connection is valid
        if ($db === null) {
            return ['success' => false, 'errors' => ['Database connection failed. Please try again later.']];
        }
        
        // Check if username already exists
        $checkQuery = "SELECT id FROM users WHERE username = :username";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            return ['success' => false, 'errors' => ['Username already exists.']];
        }
        
        // Hash password securely - use PASSWORD_DEFAULT for compatibility
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $db->beginTransaction();
        
        // Insert new user
        $insertQuery = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':username', $username);
        $insertStmt->bindParam(':password', $hashedPassword);
        $insertStmt->execute();
        
        $userId = $db->lastInsertId();
        
        // Add user to all existing shared tasks
        $tasksQuery = "SELECT id FROM shared_tasks";
        $tasksStmt = $db->prepare($tasksQuery);
        $tasksStmt->execute();
        $tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($tasks)) {
            $progressQuery = "INSERT INTO user_task_progress (task_id, user_id, is_completed) VALUES (:task_id, :user_id, FALSE)";
            $progressStmt = $db->prepare($progressQuery);
            
            foreach ($tasks as $task) {
                $progressStmt->bindParam(':task_id', $task['id']);
                $progressStmt->bindParam(':user_id', $userId);
                $progressStmt->execute();
            }
        }
        
        $db->commit();
        
        return ['success' => true, 'message' => 'Registration successful!'];
        
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollback();
        }
        if (function_exists('logSecurityEvent')) {
            logSecurityEvent('registration_error', ['error' => $e->getMessage()]);
        }
        return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
    }
}

/**
 * Authenticate user login with secure password verification
 */
function authenticateUser($username, $password) {
    if (empty($username) || empty($password)) {
        return false;
    }
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if database connection is valid
        if ($db === null) {
            return false;
        }
        
        $query = "SELECT id, username, password FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password using secure hash verification
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        
        // Add small delay to prevent timing attacks
        usleep(250000); // 250ms delay
        return false;
        
    } catch (Exception $e) {
        if (function_exists('logSecurityEvent')) {
            logSecurityEvent('auth_error', ['error' => $e->getMessage()]);
        }
        return false;
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    startSecureSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Require login - redirect if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit();
    }
}

/**
 * Login user and create secure session
 */
function loginUser($user) {
    startSecureSession();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Logout user and destroy session
 */
function logoutUser() {
    startSecureSession();
    
    // Clear all session data
    $_SESSION = array();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    header('Location: ../index.php');
    exit();
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    startSecureSession();
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get current username
 */
function getCurrentUsername() {
    startSecureSession();
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    startSecureSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    startSecureSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check session timeout (30 minutes of inactivity)
 */
function checkSessionTimeout() {
    startSecureSession();
    
    if (isset($_SESSION['last_activity'])) {
        $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 1800; // 30 minutes default
        
        if (time() - $_SESSION['last_activity'] > $timeout) {
            logoutUser();
        }
    }
    
    $_SESSION['last_activity'] = time();
}

/**
 * Rate limiting for login attempts
 */
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 900) { // 15 minutes
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if database connection is valid
        if ($db === null) {
            // If database connection fails, allow the request (fail open)
            return true;
        }
        
        // Create rate_limit table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS rate_limit (
                id INT PRIMARY KEY AUTO_INCREMENT,
                identifier VARCHAR(255) NOT NULL,
                attempts INT DEFAULT 1,
                first_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_identifier (identifier)
            )
        ";
        $db->exec($createTable);
        
        $now = time();
        $windowStart = $now - $timeWindow;
        
        // Clean old entries
        $cleanQuery = "DELETE FROM rate_limit WHERE UNIX_TIMESTAMP(first_attempt) < :window_start";
        $cleanStmt = $db->prepare($cleanQuery);
        $cleanStmt->bindParam(':window_start', $windowStart);
        $cleanStmt->execute();
        
        // Check current attempts
        $checkQuery = "SELECT attempts FROM rate_limit WHERE identifier = :identifier";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':identifier', $identifier);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($result['attempts'] >= $maxAttempts) {
                return false; // Rate limited
            }
            
            // Increment attempts
            $updateQuery = "UPDATE rate_limit SET attempts = attempts + 1 WHERE identifier = :identifier";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':identifier', $identifier);
            $updateStmt->execute();
        } else {
            // First attempt
            $insertQuery = "INSERT INTO rate_limit (identifier) VALUES (:identifier)";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->bindParam(':identifier', $identifier);
            $insertStmt->execute();
        }
        
        return true; // Not rate limited
        
    } catch (Exception $e) {
        // If rate limiting fails, allow the request (fail open)
        return true;
    }
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            $ip = trim($ips[0]);
            
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}
?>