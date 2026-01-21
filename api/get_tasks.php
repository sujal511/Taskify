<?php
/**
 * API endpoint to get user tasks
 * Returns JSON response with user's tasks
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session and check authentication
startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userId = getCurrentUserId();

try {
    $tasks = getUserTasks($userId);
    $progress = calculateProgress($userId);
    
    $response = [
        'success' => true,
        'tasks' => $tasks,
        'progress' => $progress,
        'total_tasks' => count($tasks),
        'completed_tasks' => count(array_filter($tasks, function($task) {
            return $task['is_completed'];
        }))
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>