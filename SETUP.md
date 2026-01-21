# Setup Guide - Multi-User Progress Tracker

## Prerequisites

Before setting up the application, ensure you have:

- **Web Server**: Apache or Nginx
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **Web Browser**: Modern browser with JavaScript enabled

## Installation Steps

### 1. Download and Extract Files

1. Download the project files
2. Extract to your web server directory (e.g., `htdocs`, `www`, or `public_html`)
3. Rename the folder to `progress-tracker` (optional)

### 2. Database Setup

1. **Create Database**:
   ```sql
   CREATE DATABASE progress_tracker;
   ```

2. **Import Schema**:
   - Open phpMyAdmin or your MySQL client
   - Select the `progress_tracker` database
   - Import the `database/schema.sql` file
   - Or run the SQL commands directly from the file

3. **Verify Tables**:
   - Check that `users` and `tasks` tables are created
   - Verify that sample data is inserted

### 3. Configuration

1. **Database Configuration**:
   - Open `config/database.php`
   - Update the database credentials:
     ```php
     define('DB_HOST', 'localhost');     // Your MySQL host
     define('DB_NAME', 'progress_tracker'); // Database name
     define('DB_USER', 'your_username');    // MySQL username
     define('DB_PASS', 'your_password');    // MySQL password
     ```

2. **File Permissions** (Linux/Mac):
   ```bash
   chmod 755 progress-tracker/
   chmod 644 progress-tracker/*.php
   chmod 644 progress-tracker/**/*.php
   ```

### 4. Web Server Configuration

#### Apache (.htaccess)
Create a `.htaccess` file in the root directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Nginx
Add to your server block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### 5. Testing the Installation

1. **Access the Application**:
   - Open your web browser
   - Navigate to `http://localhost/progress-tracker/`
   - You should see the login page

2. **Test Login**:
   - Use the demo credentials:
     - User 1: `user1` / `password1`
     - User 2: `user2` / `password2`

3. **Test Functionality**:
   - Create a new task
   - Mark tasks as completed
   - Check progress calculation
   - Test logout functionality

## Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database and tables exist

2. **PHP Errors**:
   - Check PHP error logs
   - Ensure PHP version is 7.4+
   - Verify all required PHP extensions are installed

3. **Permission Denied**:
   - Check file permissions
   - Ensure web server can read PHP files
   - Verify database user has proper privileges

4. **Session Issues**:
   - Check PHP session configuration
   - Ensure session directory is writable
   - Clear browser cookies and try again

### Error Logs

Check these locations for error information:
- Apache: `/var/log/apache2/error.log`
- Nginx: `/var/log/nginx/error.log`
- PHP: `/var/log/php_errors.log`

## Security Considerations

### Production Deployment

1. **Change Default Passwords**:
   - Update user passwords in the database
   - Use strong, unique passwords

2. **Database Security**:
   - Create a dedicated database user
   - Grant only necessary privileges
   - Use strong database passwords

3. **File Security**:
   - Remove or secure the `database/` folder
   - Set proper file permissions
   - Consider moving config files outside web root

4. **HTTPS**:
   - Use SSL/TLS certificates
   - Force HTTPS redirects
   - Update session cookie settings

### Recommended Production Settings

```php
// In config/database.php or a separate config file
ini_set('session.cookie_secure', 1);     // HTTPS only
ini_set('session.cookie_httponly', 1);   // No JavaScript access
ini_set('session.use_strict_mode', 1);   // Strict session handling
```

## Development Notes

### Adding New Users

To add more users beyond the default two:

1. **Database Method**:
   ```sql
   INSERT INTO users (username, password) VALUES 
   ('newuser', '$2y$10$hashedpassword');
   ```

2. **Update Authentication**:
   - Modify `includes/auth.php`
   - Update the `authenticateUser()` function
   - Add proper password hashing

### Customization

- **Styling**: Modify `assets/css/style.css`
- **Functionality**: Update `assets/js/script.js`
- **Database**: Extend schema in `database/schema.sql`
- **Features**: Add new PHP files in appropriate directories

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review error logs
3. Verify all setup steps were completed
4. Test with default configuration first