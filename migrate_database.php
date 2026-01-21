<?php
/**
 * Database Migration Script
 * Run this file once to migrate to the new shared tasks system
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Database Migration Started</h2>";
    
    // Create shared_tasks table
    $createSharedTasks = "
        CREATE TABLE IF NOT EXISTS shared_tasks (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        )
    ";
    
    $db->exec($createSharedTasks);
    echo "<p>✓ Created shared_tasks table</p>";
    
    // Create user_task_progress table
    $createUserProgress = "
        CREATE TABLE IF NOT EXISTS user_task_progress (
            id INT PRIMARY KEY AUTO_INCREMENT,
            task_id INT NOT NULL,
            user_id INT NOT NULL,
            is_completed BOOLEAN DEFAULT FALSE,
            completed_at TIMESTAMP NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (task_id) REFERENCES shared_tasks(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_task (task_id, user_id)
        )
    ";
    
    $db->exec($createUserProgress);
    echo "<p>✓ Created user_task_progress table</p>";
    
    // Check if we have any shared tasks
    $checkTasks = $db->query("SELECT COUNT(*) as count FROM shared_tasks");
    $taskCount = $checkTasks->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($taskCount == 0) {
        // Insert sample shared tasks
        $sampleTasks = [
            ['Complete Project Documentation', 'Write comprehensive documentation for the project', 1],
            ['Design Database Schema', 'Create and implement the database structure', 1],
            ['Implement User Authentication', 'Build secure login and session management', 2],
            ['Create Frontend Interface', 'Design and develop the user interface', 2],
            ['Test Application Features', 'Perform thorough testing of all functionalities', 1],
            ['Prepare Presentation', 'Create slides for project demonstration', 2]
        ];
        
        $insertTask = $db->prepare("INSERT INTO shared_tasks (title, description, created_by) VALUES (?, ?, ?)");
        
        foreach ($sampleTasks as $task) {
            $insertTask->execute($task);
        }
        
        echo "<p>✓ Inserted sample shared tasks</p>";
        
        // Insert sample progress data
        $sampleProgress = [
            [1, 1, FALSE, NULL], [1, 2, FALSE, NULL],
            [2, 1, TRUE, date('Y-m-d H:i:s')], [2, 2, FALSE, NULL],
            [3, 1, FALSE, NULL], [3, 2, TRUE, date('Y-m-d H:i:s')],
            [4, 1, TRUE, date('Y-m-d H:i:s')], [4, 2, FALSE, NULL],
            [5, 1, FALSE, NULL], [5, 2, FALSE, NULL],
            [6, 1, FALSE, NULL], [6, 2, FALSE, NULL]
        ];
        
        $insertProgress = $db->prepare("INSERT INTO user_task_progress (task_id, user_id, is_completed, completed_at) VALUES (?, ?, ?, ?)");
        
        foreach ($sampleProgress as $progress) {
            $insertProgress->execute($progress);
        }
        
        echo "<p>✓ Inserted sample progress data</p>";
    } else {
        echo "<p>✓ Shared tasks already exist</p>";
    }
    
    // Check if old tasks table exists and migrate if needed
    $checkOldTasks = $db->query("SHOW TABLES LIKE 'tasks'");
    if ($checkOldTasks->rowCount() > 0) {
        echo "<p>⚠ Old 'tasks' table found. You may want to migrate data manually.</p>";
        echo "<p>Consider running the migration SQL script or backing up your data.</p>";
    }
    
    echo "<h3>✅ Migration Completed Successfully!</h3>";
    echo "<p><a href='index.php'>Go to Application</a></p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Migration Failed</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}
?>