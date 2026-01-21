<?php
/**
 * API endpoint to update task status
 * Accepts POST requests with task ID and completion status
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$userId = getCurrentUserId();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    // Fallback to POST data
    $input = $_POST;
}

$taskId = isset($input['task_id']) ? (int)$input['task_id'] : 0;
$isCompleted = isset($input['is_completed']) ? (bool)$input['is_completed'] : false;

// Validate input
if ($taskId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid task ID']);
    exit();
}

try {
    $success = updateTaskStatus($taskId, $userId, $isCompleted);
    
    if ($success) {
        $progress = calculateProgress($userId);
        $tasks = getUserTasks($userId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Task updated successfully',
            'progress' => $progress,
            'completed_tasks' => count(array_filter($tasks, function($task) {
                return $task['is_completed'];
            }))
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Task not found or access denied']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>