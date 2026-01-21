<?php
require_once 'includes/auth.php';

// Log security event if function exists
if (function_exists('logSecurityEvent')) {
    logSecurityEvent('user_logout', ['user_id' => getCurrentUserId()]);
}

logoutUser();
?>