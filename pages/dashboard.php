<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check session timeout and require login
checkSessionTimeout();
requireLogin();

$userId = getCurrentUserId();
$username = getCurrentUsername();
$tasks = getSharedTasksWithProgress($userId);
$myProgress = calculateUserProgress($userId);
$otherUserProgress = getOtherUserProgress($userId);
$otherUserName = getOtherUserName($userId);

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_task'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        
        if (validateTaskTitle($title)) {
            if (createSharedTask($userId, $title, $description)) {
                header('Location: dashboard.php?success=task_created');
                exit();
            } else {
                $error = 'Failed to create task. Please try again.';
            }
        } else {
            $error = 'Invalid task title. Please check the requirements.';
        }
    }
}

// Handle task status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_task'])) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        $taskId = (int)$_POST['task_id'];
        $isCompleted = isset($_POST['is_completed']) ? 1 : 0;
        $notes = sanitizeInput($_POST['notes'] ?? '');
        
        if (updateUserTaskProgress($taskId, $userId, $isCompleted, $notes)) {
            header('Location: dashboard.php?success=task_updated');
            exit();
        } else {
            $error = 'Failed to update task. Please try again.';
        }
    }
}

// Calculate statistics
$totalTasks = count($tasks);
$myCompletedTasks = count(array_filter($tasks, function($task) { return $task['my_progress']; }));
$overallCompletedTasks = 0;
foreach ($tasks as $task) {
    if ($task['total_completed'] == 2) { // Both users completed
        $overallCompletedTasks++;
    }
}

// Success messages
$successMessage = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'task_created':
            $successMessage = 'Task created successfully and shared with all users!';
            break;
        case 'task_updated':
            $successMessage = 'Task progress updated successfully!';
            break;
    }
}
?>
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Collaborative Progress Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <div class="header-left">
                    <h1><i class="fas fa-tasks"></i> Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
                    <p class="header-subtitle">Collaborative Task Management</p>
                </div>
                <div class="header-actions">
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span>Logged in as <?php echo htmlspecialchars($username); ?></span>
                    </div>
                    <a href="../logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </header>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Progress Comparison Section -->
        <section class="progress-comparison-section">
            <div class="progress-comparison-container">
                <div class="progress-card my-progress">
                    <div class="progress-header">
                        <h3><i class="fas fa-user"></i> Your Progress</h3>
                        <span class="progress-percentage"><?php echo $myProgress; ?>%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar">
                            <div class="progress-fill my-fill" style="width: <?php echo $myProgress; ?>%"></div>
                        </div>
                    </div>
                    <div class="progress-stats">
                        <div class="stat">
                            <span class="stat-number"><?php echo $myCompletedTasks; ?></span>
                            <span class="stat-label">Completed</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number"><?php echo $totalTasks; ?></span>
                            <span class="stat-label">Total Tasks</span>
                        </div>
                    </div>
                </div>

                <div class="vs-divider">
                    <div class="vs-circle">VS</div>
                </div>

                <div class="progress-card other-progress">
                    <div class="progress-header">
                        <h3><i class="fas fa-user-friends"></i> <?php echo htmlspecialchars($otherUserName); ?>'s Progress</h3>
                        <span class="progress-percentage"><?php echo $otherUserProgress; ?>%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar">
                            <div class="progress-fill other-fill" style="width: <?php echo $otherUserProgress; ?>%"></div>
                        </div>
                    </div>
                    <div class="progress-stats">
                        <div class="stat">
                            <span class="stat-number"><?php echo count(array_filter($tasks, function($task) use ($userId) { 
                                return $task['total_completed'] > ($task['my_progress'] ? 1 : 0); 
                            })); ?></span>
                            <span class="stat-label">Completed</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number"><?php echo $totalTasks; ?></span>
                            <span class="stat-label">Total Tasks</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overall Progress -->
            <div class="overall-progress-card">
                <h3><i class="fas fa-chart-line"></i> Team Progress</h3>
                <div class="team-stats">
                    <div class="team-stat">
                        <i class="fas fa-check-double"></i>
                        <div>
                            <span class="team-stat-number"><?php echo $overallCompletedTasks; ?></span>
                            <span class="team-stat-label">Fully Completed Tasks</span>
                        </div>
                    </div>
                    <div class="team-stat">
                        <i class="fas fa-clock"></i>
                        <div>
                            <span class="team-stat-number"><?php echo $totalTasks - $overallCompletedTasks; ?></span>
                            <span class="team-stat-label">In Progress Tasks</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Task Creation Form -->
        <section class="task-form-section">
            <div class="form-card">
                <h2><i class="fas fa-plus-circle"></i> Add New Shared Task</h2>
                <form method="POST" class="task-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title"><i class="fas fa-heading"></i> Task Title *</label>
                            <input type="text" id="title" name="title" required maxlength="255" placeholder="Enter task title...">
                        </div>
                        <div class="form-group">
                            <label for="description"><i class="fas fa-align-left"></i> Description</label>
                            <textarea id="description" name="description" rows="3" placeholder="Describe the task..."></textarea>
                        </div>
                    </div>
                    <button type="submit" name="create_task" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Shared Task
                    </button>
                </form>
            </div>
        </section>

        <!-- Tasks List -->
        <section class="tasks-section">
            <div class="tasks-card">
                <h2><i class="fas fa-list-check"></i> Shared Tasks</h2>
                <?php if (empty($tasks)): ?>
                    <div class="empty-state">
                        <i class="fas fa-tasks empty-icon"></i>
                        <p>No shared tasks yet. Create your first task above!</p>
                    </div>
                <?php else: ?>
                    <div class="tasks-list">
                        <?php foreach ($tasks as $task): ?>
                            <div class="task-item <?php echo $task['my_progress'] ? 'my-completed' : ''; ?> <?php echo $task['total_completed'] == 2 ? 'fully-completed' : ''; ?>">
                                <div class="task-content">
                                    <div class="task-header">
                                        <h3 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                                        <div class="task-collaboration-status">
                                            <div class="collaboration-indicator">
                                                <span class="collab-count"><?php echo $task['total_completed']; ?>/2</span>
                                                <i class="fas fa-users"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($task['description'])): ?>
                                        <p class="task-description"><?php echo htmlspecialchars($task['description']); ?></p>
                                    <?php endif; ?>

                                    <div class="task-progress-indicators">
                                        <div class="user-progress-item <?php echo $task['my_progress'] ? 'completed' : 'pending'; ?>">
                                            <i class="fas fa-user"></i>
                                            <span>You: <?php echo $task['my_progress'] ? 'Completed' : 'Pending'; ?></span>
                                            <?php if ($task['my_progress'] && $task['my_completed_at']): ?>
                                                <small class="completion-time">
                                                    <i class="fas fa-clock"></i>
                                                    <?php echo date('M j, Y H:i', strtotime($task['my_completed_at'])); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="user-progress-item <?php echo ($task['total_completed'] > ($task['my_progress'] ? 1 : 0)) ? 'completed' : 'pending'; ?>">
                                            <i class="fas fa-user-friends"></i>
                                            <span><?php echo htmlspecialchars($otherUserName); ?>: 
                                                <?php echo ($task['total_completed'] > ($task['my_progress'] ? 1 : 0)) ? 'Completed' : 'Pending'; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="task-actions">
                                        <form method="POST" class="task-action-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <div class="action-group">
                                                <label class="checkbox-container">
                                                    <input type="checkbox" name="is_completed" 
                                                           <?php echo $task['my_progress'] ? 'checked' : ''; ?>
                                                           onchange="this.form.submit()">
                                                    <span class="checkmark"></span>
                                                    <span class="checkbox-label">Mark as completed</span>
                                                </label>
                                            </div>
                                            <input type="hidden" name="update_task" value="1">
                                        </form>
                                    </div>

                                    <div class="task-meta">
                                        <span class="task-creator">
                                            <i class="fas fa-user-plus"></i>
                                            Created by: <?php echo htmlspecialchars($task['created_by_name']); ?>
                                        </span>
                                        <span class="task-date">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('M j, Y', strtotime($task['created_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>