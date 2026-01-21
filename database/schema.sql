-- Multi-User Progress Tracker Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS if0_40943419_progress_tracker;
USE if0_40943419_progress_tracker;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Shared tasks table
CREATE TABLE shared_tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- User task progress table (tracks individual progress on shared tasks)
CREATE TABLE user_task_progress (
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

-- Insert default admin user (remove in production)
INSERT INTO users (username, password) VALUES 
('admin', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj/VjWZifZTy'); -- password: admin123 (change in production)

-- Insert sample shared tasks
INSERT INTO shared_tasks (title, description, created_by) VALUES
('Complete Project Documentation', 'Write comprehensive documentation for the project', 1),
('Design Database Schema', 'Create and implement the database structure', 1),
('Implement User Authentication', 'Build secure login and session management', 1),
('Create Frontend Interface', 'Design and develop the user interface', 1),
('Test Application Features', 'Perform thorough testing of all functionalities', 1),
('Prepare Presentation', 'Create slides for project demonstration', 1);

-- Insert user progress for shared tasks (only for admin user initially)
INSERT INTO user_task_progress (task_id, user_id, is_completed, completed_at) VALUES
(1, 1, FALSE, NULL),
(2, 1, TRUE, NOW()),
(3, 1, TRUE, NOW()),
(4, 1, TRUE, NOW()),
(5, 1, FALSE, NULL),
(6, 1, FALSE, NULL);