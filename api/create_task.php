<?php
/**
 * API endpoint to create a new task
 * Accepts POST requests with task data
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

$title = isset($input['title']) ? sanitizeInput($input['title']) : '';
$description = isset($input['description']) ? sanitizeInput($input['description']) : '';

// Validate input
if (empty($title)) {
    http_response_code(400);
    echo json_encode(['error' => 'Title is required']);
    exit();
}

if (!validateTaskTitle($title)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid title format or length']);
    exit();
}

try {
    $success = createTask($userId, $title, $description);
    
    if ($success) {
        $tasks = getUserTasks($userId);
        $progress = calculateProgress($userId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Task created successfully',
            'progress' => $progress,
            'total_tasks' => count($tasks)
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create task']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>