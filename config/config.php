<?php
/**
 * Application Configuration
 * Update these settings for production deployment
 */

// Environment settings
define('APP_ENV', 'development'); // Change to 'production' for live deployment
define('APP_DEBUG', true); // Set to false for production
define('APP_NAME', 'Collaborative Progress Tracker');
define('APP_VERSION', '1.0.0');

// Security settings
define('SECURE_COOKIES', false); // Set to true in production with HTTPS
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('RATE_LIMIT_WINDOW', 900); // 15 minutes

// Password requirements
define('MIN_PASSWORD_LENGTH', 8);
define('REQUIRE_STRONG_PASSWORD', true);

// Logging settings
define('LOG_ERRORS', true);
define('LOG_FILE', __DIR__ . '/../logs/app.log');

// Create logs directory if it doesn't exist
if (!file_exists(dirname(LOG_FILE))) {
    @mkdir(dirname(LOG_FILE), 0755, true);
}

// Error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    if (is_writable(dirname(LOG_FILE))) {
        ini_set('error_log', LOG_FILE);
    }
}

// Timezone
date_default_timezone_set('UTC');

/**
 * Sanitize output for display
 */
function sanitizeOutput($data) {
    if (is_array($data)) {
        return array_map('sanitizeOutput', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate secure random token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Log security events
 */
function logSecurityEvent($event, $details = []) {
    if (!LOG_ERRORS) return;
    
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'details' => $details
    ];
    
    $logMessage = json_encode($logData);
    
    if (is_writable(dirname(LOG_FILE))) {
        error_log("SECURITY: $logMessage", 3, LOG_FILE);
    }
}
?>