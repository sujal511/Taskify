-- Migration script to convert existing database to shared tasks system
-- Run this if you already have the old database structure

USE if0_40943419_progress_tracker;

-- Create shared_tasks table
CREATE TABLE IF NOT EXISTS shared_tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create user_task_progress table
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
);

-- Migrate existing tasks to shared_tasks (if old tasks table exists)
INSERT IGNORE INTO shared_tasks (title, description, created_by, created_at)
SELECT title, description, user_id, created_at 
FROM tasks 
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'if0_40943419_progress_tracker' AND table_name = 'tasks');

-- Migrate existing task progress to user_task_progress
INSERT IGNORE INTO user_task_progress (task_id, user_id, is_completed, completed_at, created_at)
SELECT st.id, t.user_id, t.is_completed, 
       CASE WHEN t.is_completed = 1 THEN t.updated_at ELSE NULL END,
       t.created_at
FROM tasks t
JOIN shared_tasks st ON st.title = t.title AND st.description = t.description AND st.created_by = t.user_id
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'if0_40943419_progress_tracker' AND table_name = 'tasks');

-- Add progress entries for the other user on all existing tasks
INSERT IGNORE INTO user_task_progress (task_id, user_id, is_completed)
SELECT st.id, 
       CASE WHEN st.created_by = 1 THEN 2 ELSE 1 END as other_user_id,
       FALSE
FROM shared_tasks st
WHERE NOT EXISTS (
    SELECT 1 FROM user_task_progress utp 
    WHERE utp.task_id = st.id 
    AND utp.user_id = CASE WHEN st.created_by = 1 THEN 2 ELSE 1 END
);

-- Insert sample shared tasks if no tasks exist
INSERT IGNORE INTO shared_tasks (title, description, created_by) 
SELECT * FROM (
    SELECT 'Complete Project Documentation' as title, 'Write comprehensive documentation for the project' as description, 1 as created_by
    UNION ALL
    SELECT 'Design Database Schema', 'Create and implement the database structure', 1
    UNION ALL
    SELECT 'Implement User Authentication', 'Build secure login and session management', 2
    UNION ALL
    SELECT 'Create Frontend Interface', 'Design and develop the user interface', 2
    UNION ALL
    SELECT 'Test Application Features', 'Perform thorough testing of all functionalities', 1
    UNION ALL
    SELECT 'Prepare Presentation', 'Create slides for project demonstration', 2
) AS sample_tasks
WHERE NOT EXISTS (SELECT 1 FROM shared_tasks);

-- Insert sample progress data if no progress exists
INSERT IGNORE INTO user_task_progress (task_id, user_id, is_completed, completed_at) 
SELECT st.id, 1, 
       CASE 
           WHEN st.title = 'Design Database Schema' THEN TRUE
           WHEN st.title = 'Create Frontend Interface' THEN TRUE
           ELSE FALSE 
       END,
       CASE 
           WHEN st.title = 'Design Database Schema' THEN NOW()
           WHEN st.title = 'Create Frontend Interface' THEN NOW()
           ELSE NULL 
       END
FROM shared_tasks st
WHERE NOT EXISTS (SELECT 1 FROM user_task_progress WHERE user_id = 1);

INSERT IGNORE INTO user_task_progress (task_id, user_id, is_completed, completed_at) 
SELECT st.id, 2, 
       CASE 
           WHEN st.title = 'Implement User Authentication' THEN TRUE
           ELSE FALSE 
       END,
       CASE 
           WHEN st.title = 'Implement User Authentication' THEN NOW()
           ELSE NULL 
       END
FROM shared_tasks st
WHERE NOT EXISTS (SELECT 1 FROM user_task_progress WHERE user_id = 2);

-- Optional: Drop old tasks table (uncomment if you want to remove it)
-- DROP TABLE IF EXISTS tasks;