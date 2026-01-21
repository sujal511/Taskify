<?php
/**
 * Production Setup Script
 * Run this once to set up the application for production use
 */

// Include configuration
require_once 'config/config.php';
require_once 'config/database.php';

// Only allow this script to run in development or with special parameter
if (APP_ENV === 'production' && !isset($_GET['force'])) {
    die('This script cannot be run in production mode. Add ?force=1 if you really need to run it.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Setup - Progress Tracker</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f7fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Production Setup</h1>
        
        <?php
        $errors = [];
        $warnings = [];
        $success = [];
        
        try {
            echo "<div class='step'>";
            echo "<h3>Step 1: Database Connection Test</h3>";
            
            $database = new Database();
            $db = $database->getConnection();
            
            if ($db) {
                echo "<p class='success'>✓ Database connection successful</p>";
                $success[] = "Database connection established";
            } else {
                echo "<p class='error'>✗ Database connection failed</p>";
                $errors[] = "Cannot connect to database";
            }
            echo "</div>";
            
            echo "<div class='step'>";
            echo "<h3>Step 2: Database Tables Check</h3>";
            
            // Check if tables exist
            $tables = ['users', 'shared_tasks', 'user_task_progress'];
            $existingTables = [];
            
            foreach ($tables as $table) {
                $stmt = $db->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    echo "<p class='success'>✓ Table '$table' exists</p>";
                    $existingTables[] = $table;
                } else {
                    echo "<p class='error'>✗ Table '$table' missing</p>";
                    $errors[] = "Table '$table' is missing";
                }
            }
            echo "</div>";
            
            echo "<div class='step'>";
            echo "<h3>Step 3: Admin User Check</h3>";
            
            if (in_array('users', $existingTables)) {
                $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['count'] > 0) {
                    echo "<p class='success'>✓ Admin user exists</p>";
                    echo "<p class='warning'>⚠ Remember to change the default admin password!</p>";
                    $warnings[] = "Change default admin password";
                } else {
                    echo "<p class='warning'>⚠ No admin user found</p>";
                    $warnings[] = "No admin user found - you may need to create one";
                }
            }
            echo "</div>";
            
            echo "<div class='step'>";
            echo "<h3>Step 4: File Permissions Check</h3>";
            
            $checkDirs = [
                'logs' => 'Logs directory',
                'config' => 'Config directory',
                'assets' => 'Assets directory'
            ];
            
            foreach ($checkDirs as $dir => $name) {
                if (is_dir($dir)) {
                    if (is_writable($dir)) {
                        echo "<p class='success'>✓ $name is writable</p>";
                    } else {
                        echo "<p class='warning'>⚠ $name is not writable</p>";
                        $warnings[] = "$name needs write permissions";
                    }
                } else {
                    echo "<p class='error'>✗ $name does not exist</p>";
                    $errors[] = "$name directory missing";
                }
            }
            echo "</div>";
            
            echo "<div class='step'>";
            echo "<h3>Step 5: PHP Configuration Check</h3>";
            
            $phpChecks = [
                'PDO' => extension_loaded('pdo'),
                'PDO MySQL' => extension_loaded('pdo_mysql'),
                'OpenSSL' => extension_loaded('openssl'),
                'JSON' => extension_loaded('json'),
                'Session' => extension_loaded('session'),
                'Hash' => extension_loaded('hash')
            ];
            
            foreach ($phpChecks as $ext => $loaded) {
                if ($loaded) {
                    echo "<p class='success'>✓ $ext extension loaded</p>";
                } else {
                    echo "<p class='error'>✗ $ext extension missing</p>";
                    $errors[] = "$ext PHP extension required";
                }
            }
            
            // Check PHP version
            $phpVersion = PHP_VERSION;
            if (version_compare($phpVersion, '7.4.0', '>=')) {
                echo "<p class='success'>✓ PHP version $phpVersion is supported</p>";
            } else {
                echo "<p class='error'>✗ PHP version $phpVersion is too old (7.4+ required)</p>";
                $errors[] = "PHP version too old";
            }
            echo "</div>";
            
            echo "<div class='step'>";
            echo "<h3>Step 6: Security Configuration Check</h3>";
            
            if (APP_ENV === 'production') {
                echo "<p class='success'>✓ Application is in production mode</p>";
            } else {
                echo "<p class='warning'>⚠ Application is in development mode</p>";
                $warnings[] = "Change APP_ENV to 'production' in config/config.php";
            }
            
            if (!APP_DEBUG) {
                echo "<p class='success'>✓ Debug mode is disabled</p>";
            } else {
                echo "<p class='warning'>⚠ Debug mode is enabled</p>";
                $warnings[] = "Disable debug mode for production";
            }
            
            if (isHTTPS()) {
                echo "<p class='success'>✓ HTTPS is enabled</p>";
            } else {
                echo "<p class='warning'>⚠ HTTPS is not enabled</p>";
                $warnings[] = "Enable HTTPS for production";
            }
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='step'>";
            echo "<p class='error'>✗ Setup failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            $errors[] = $e->getMessage();
            echo "</div>";
        }
        
        // Summary
        echo "<div class='step'>";
        echo "<h3>📋 Setup Summary</h3>";
        
        if (empty($errors)) {
            echo "<p class='success'><strong>✓ Setup completed successfully!</strong></p>";
            echo "<p>Your application is ready for production use.</p>";
        } else {
            echo "<p class='error'><strong>✗ Setup completed with errors:</strong></p>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li class='error'>$error</li>";
            }
            echo "</ul>";
        }
        
        if (!empty($warnings)) {
            echo "<p class='warning'><strong>⚠ Warnings:</strong></p>";
            echo "<ul>";
            foreach ($warnings as $warning) {
                echo "<li class='warning'>$warning</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
        
        if (empty($errors)) {
            echo "<div class='step'>";
            echo "<h3>🎯 Next Steps</h3>";
            echo "<ol>";
            echo "<li>Change the default admin password</li>";
            echo "<li>Configure your web server (Apache/Nginx)</li>";
            echo "<li>Set up SSL certificate</li>";
            echo "<li>Configure backup procedures</li>";
            echo "<li>Set up monitoring</li>";
            echo "<li>Delete this setup file for security</li>";
            echo "</ol>";
            echo "</div>";
        }
        ?>
        
        <div class="step">
            <h3>🔗 Quick Actions</h3>
            <a href="index.php" class="btn">Go to Application</a>
            <a href="register.php" class="btn">Register New User</a>
            <?php if (empty($errors)): ?>
                <a href="?delete_setup=1" class="btn" style="background: #dc3545;" 
                   onclick="return confirm('Are you sure you want to delete this setup file?')">Delete Setup File</a>
            <?php endif; ?>
        </div>
        
        <div class="step">
            <h3>📚 Documentation</h3>
            <p>For detailed deployment instructions, see <code>DEPLOYMENT.md</code></p>
            <p>For application features, see <code>FEATURES.md</code></p>
            <p>For general information, see <code>README.md</code></p>
        </div>
    </div>
</body>
</html>

<?php
// Handle setup file deletion
if (isset($_GET['delete_setup']) && $_GET['delete_setup'] == '1') {
    if (unlink(__FILE__)) {
        echo "<script>alert('Setup file deleted successfully!'); window.location.href = 'index.php';</script>";
    } else {
        echo "<script>alert('Failed to delete setup file. Please delete it manually.');</script>";
    }
}
?>