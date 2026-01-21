<?php
require_once 'includes/auth.php';

startSecureSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Rate limiting
        $clientIP = getClientIP();
        if (!checkRateLimit('login_' . $clientIP)) {
            $error = 'Too many login attempts. Please try again in 15 minutes.';
        } else {
            $username = sanitizeInput($_POST['username']);
            $password = $_POST['password'];
            
            if (empty($username) || empty($password)) {
                $error = 'Please fill in all fields.';
            } else {
                $user = authenticateUser($username, $password);
                
                if ($user) {
                    loginUser($user);
                    header('Location: pages/dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid username or password.';
                }
            }
        }
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Collaborative Progress Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><i class="fas fa-tasks"></i> Progress Tracker</h1>
                <p>Collaborative task management platform</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" id="username" name="username" required 
                           autocomplete="username"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-input-container">
                        <input type="password" id="password" name="password" required 
                               autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="form-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
            
            <div class="demo-info">
                <h3><i class="fas fa-info-circle"></i> Demo Information</h3>
                <p>This is a collaborative task management system where multiple users can work on shared tasks while tracking individual progress.</p>
                <div class="demo-features">
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <span>Multi-user collaboration</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Individual progress tracking</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-tasks"></i>
                        <span>Shared task management</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Form submission handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
            loginBtn.disabled = true;
        });

        // Auto-focus username field
        document.getElementById('username').focus();
    </script>

    <style>
        .password-input-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #718096;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #4a5568;
        }

        .password-input-container input {
            padding-right: 45px;
        }

        .form-footer {
            text-align: center;
            padding: 20px 30px;
            border-top: 1px solid #e2e8f0;
            background: #f7fafc;
        }

        .form-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .demo-info {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 25px 30px;
            border-top: 1px solid #e2e8f0;
        }

        .demo-info h3 {
            margin-bottom: 15px;
            color: #2d3748;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-info p {
            color: #4a5568;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .demo-features {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #4a5568;
            font-size: 0.9rem;
        }

        .feature-item i {
            color: #667eea;
            width: 16px;
        }

        .error-message {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 480px) {
            .demo-features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>