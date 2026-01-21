<?php
/**
 * Utility Functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get all shared tasks with user progress
 */
function getSharedTasksWithProgress($userId) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT 
                st.id,
                st.title,
                st.description,
                st.created_by,
                st.created_at,
                u_creator.username as created_by_name,
                utp.is_completed as my_progress,
                utp.completed_at as my_completed_at,
                utp.notes as my_notes,
                (SELECT COUNT(*) FROM user_task_progress WHERE task_id = st.id AND is_completed = 1) as total_completed,
                (SELECT COUNT(*) FROM user_task_progress WHERE task_id = st.id) as total_assigned
              FROM shared_tasks st
              LEFT JOIN user_task_progress utp ON st.id = utp.task_id AND utp.user_id = :user_id
              LEFT JOIN users u_creator ON st.created_by = u_creator.id
              ORDER BY st.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Create a new shared task
 */
function createSharedTask($createdBy, $title, $description = '') {
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        $db->beginTransaction();
        
        // Create the shared task
        $query = "INSERT INTO shared_tasks (title, description, created_by) VALUES (:title, :description, :created_by)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':created_by', $createdBy);
        $stmt->execute();
        
        $taskId = $db->lastInsertId();
        
        // Add progress entries for both users
        $progressQuery = "INSERT INTO user_task_progress (task_id, user_id, is_completed) VALUES (:task_id, :user_id, FALSE)";
        $progressStmt = $db->prepare($progressQuery);
        
        // Add for user 1
        $progressStmt->bindParam(':task_id', $taskId);
        $userId1 = 1;
        $progressStmt->bindParam(':user_id', $userId1);
        $progressStmt->execute();
        
        // Add for user 2
        $progressStmt->bindParam(':task_id', $taskId);
        $userId2 = 2;
        $progressStmt->bindParam(':user_id', $userId2);
        $progressStmt->execute();
        
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

/**
 * Update user's progress on a shared task
 */
function updateUserTaskProgress($taskId, $userId, $isCompleted, $notes = '') {
    $database = new Database();
    $db = $database->getConnection();
    
    $completedAt = $isCompleted ? date('Y-m-d H:i:s') : null;
    
    $query = "UPDATE user_task_progress 
              SET is_completed = :is_completed, 
                  completed_at = :completed_at,
                  notes = :notes
              WHERE task_id = :task_id AND user_id = :user_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':is_completed', $isCompleted, PDO::PARAM_BOOL);
    $stmt->bindParam(':completed_at', $completedAt);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':task_id', $taskId);
    $stmt->bindParam(':user_id', $userId);
    
    return $stmt->execute();
}

/**
 * Calculate user's individual progress percentage
 */
function calculateUserProgress($userId) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get total tasks assigned to user
    $totalQuery = "SELECT COUNT(*) as total FROM user_task_progress WHERE user_id = :user_id";
    $totalStmt = $db->prepare($totalQuery);
    $totalStmt->bindParam(':user_id', $userId);
    $totalStmt->execute();
    $totalResult = $totalStmt->fetch(PDO::FETCH_ASSOC);
    $totalTasks = $totalResult['total'];
    
    if ($totalTasks == 0) {
        return 0;
    }
    
    // Get completed tasks by user
    $completedQuery = "SELECT COUNT(*) as completed FROM user_task_progress WHERE user_id = :user_id AND is_completed = 1";
    $completedStmt = $db->prepare($completedQuery);
    $completedStmt->bindParam(':user_id', $userId);
    $completedStmt->execute();
    $completedResult = $completedStmt->fetch(PDO::FETCH_ASSOC);
    $completedTasks = $completedResult['completed'];
    
    return round(($completedTasks / $totalTasks) * 100, 1);
}

/**
 * Get other user's progress on the same tasks
 */
function getOtherUserProgress($currentUserId) {
    $otherUserId = ($currentUserId == 1) ? 2 : 1;
    return calculateUserProgress($otherUserId);
}

/**
 * Get other user's name
 */
function getOtherUserName($currentUserId) {
    $database = new Database();
    $db = $database->getConnection();
    
    $otherUserId = ($currentUserId == 1) ? 2 : 1;
    
    $query = "SELECT username FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $otherUserId);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['username'] : 'Other User';
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate task title
 */
function validateTaskTitle($title) {
    return !empty($title) && strlen($title) <= 255;
}
?>