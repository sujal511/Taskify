<?php
require_once 'includes/auth.php';

startSecureSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit();
}

$errors = [];
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Rate limiting
        $clientIP = getClientIP();
        if (!checkRateLimit('register_' . $clientIP, 3, 3600)) { // 3 attempts per hour
            $errors[] = 'Too many registration attempts. Please try again later.';
        } else {
            $username = sanitizeInput($_POST['username']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            
            $result = registerUser($username, $password, $confirmPassword);
            
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $errors = $result['errors'];
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
    <title>Register - Collaborative Progress Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="robots" content="noindex, nofollow">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><i class="fas fa-user-plus"></i> Create Account</h1>
                <p>Join the collaborative task management platform</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                    <br><br>
                    <a href="index.php" class="btn btn-primary" style="display: inline-block; margin-top: 10px;">
                        <i class="fas fa-sign-in-alt"></i> Login Now
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" class="login-form" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" id="username" name="username" required 
                               minlength="3" maxlength="50" 
                               pattern="[a-zA-Z0-9_]+" 
                               title="Username can only contain letters, numbers, and underscores"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        <small class="form-help">3-50 characters, letters, numbers, and underscores only</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="password-input-container">
                            <input type="password" id="password" name="password" required 
                                   minlength="8" 
                                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
                                   title="Password must contain at least 8 characters with uppercase, lowercase, and number">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-help">At least 8 characters with uppercase, lowercase, and number</small>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirm Password
                        </label>
                        <div class="password-input-container">
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-match" id="passwordMatch"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
                
                <div class="form-footer">
                    <p>Already have an account? <a href="index.php">Login here</a></p>
                </div>
            <?php endif; ?>
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

        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            let feedback = [];

            if (password.length >= 8) strength++;
            else feedback.push('At least 8 characters');

            if (/[a-z]/.test(password)) strength++;
            else feedback.push('Lowercase letter');

            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('Uppercase letter');

            if (/\d/.test(password)) strength++;
            else feedback.push('Number');

            if (/[^a-zA-Z0-9]/.test(password)) {
                strength++;
                feedback = feedback.filter(f => f !== 'Special character');
            }

            return { strength, feedback };
        }

        // Real-time password validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            const { strength, feedback } = checkPasswordStrength(password);

            let strengthText = '';
            let strengthClass = '';

            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }

            if (strength < 2) {
                strengthText = 'Weak';
                strengthClass = 'strength-weak';
            } else if (strength < 4) {
                strengthText = 'Medium';
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Strong';
                strengthClass = 'strength-strong';
            }

            strengthDiv.innerHTML = `
                <div class="strength-indicator ${strengthClass}">
                    <div class="strength-bar">
                        <div class="strength-fill" style="width: ${(strength / 4) * 100}%"></div>
                    </div>
                    <span class="strength-text">${strengthText}</span>
                </div>
                ${feedback.length > 0 ? `<div class="strength-feedback">Missing: ${feedback.join(', ')}</div>` : ''}
            `;
        });

        // Password match validation
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');

            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                return true;
            }

            if (password === confirmPassword) {
                matchDiv.innerHTML = '<div class="match-success"><i class="fas fa-check"></i> Passwords match</div>';
                return true;
            } else {
                matchDiv.innerHTML = '<div class="match-error"><i class="fas fa-times"></i> Passwords do not match</div>';
                return false;
            }
        }

        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
        document.getElementById('password').addEventListener('input', checkPasswordMatch);

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const { strength } = checkPasswordStrength(password);
            
            if (strength < 3) {
                e.preventDefault();
                alert('Please choose a stronger password.');
                return;
            }

            if (!checkPasswordMatch()) {
                e.preventDefault();
                alert('Passwords do not match.');
                return;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            submitBtn.disabled = true;
        });
    </script>

    <style>
        .form-help {
            display: block;
            font-size: 0.8rem;
            color: #718096;
            margin-top: 5px;
        }

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

        .password-strength {
            margin-top: 8px;
        }

        .strength-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .strength-bar {
            flex: 1;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: width 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak .strength-fill { background: #f56565; }
        .strength-medium .strength-fill { background: #ed8936; }
        .strength-strong .strength-fill { background: #48bb78; }

        .strength-text {
            font-size: 0.8rem;
            font-weight: 600;
            min-width: 60px;
        }

        .strength-weak .strength-text { color: #f56565; }
        .strength-medium .strength-text { color: #ed8936; }
        .strength-strong .strength-text { color: #48bb78; }

        .strength-feedback {
            font-size: 0.75rem;
            color: #718096;
        }

        .password-match {
            margin-top: 5px;
            font-size: 0.8rem;
        }

        .match-success {
            color: #48bb78;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .match-error {
            color: #f56565;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .success-message {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: #22543d;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 30px;
            border: 1px solid #9ae6b4;
            font-weight: 500;
            text-align: center;
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
    </style>
</body>
</html>