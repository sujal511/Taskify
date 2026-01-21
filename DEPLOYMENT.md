# Production Deployment Guide

## 🚀 Pre-Deployment Checklist

### 1. Environment Configuration
- [ ] Update `config/config.php`:
  - Change `APP_ENV` to `'production'`
  - Set `APP_DEBUG` to `false`
  - Configure proper error logging
- [ ] Update `config/database.php` with production database credentials
- [ ] Ensure all sensitive information is removed from code

### 2. Security Configuration
- [ ] Change default admin password in database
- [ ] Enable HTTPS and update security headers
- [ ] Configure proper file permissions
- [ ] Review and update `.htaccess` settings
- [ ] Enable rate limiting and security modules

### 3. Database Setup
- [ ] Create production database
- [ ] Import schema from `database/schema.sql`
- [ ] Update database credentials
- [ ] Test database connection

## 🔧 Server Requirements

### Minimum Requirements
- **PHP**: 7.4 or higher (8.0+ recommended)
- **MySQL**: 5.7 or higher (8.0+ recommended)
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum
- **Storage**: 100MB disk space

### Required PHP Extensions
- PDO and PDO_MySQL
- OpenSSL
- JSON
- Session
- Hash
- Filter
- Ctype

### Recommended PHP Extensions
- OPcache (for performance)
- APCu (for caching)
- Imagick (for future image handling)

## 📁 File Permissions

Set proper file permissions for security:

```bash
# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make specific directories writable
chmod 755 logs/
chmod 644 config/database.php

# Protect sensitive files
chmod 600 config/database.php
chmod 600 .htaccess
```

## 🗄️ Database Configuration

### 1. Create Database
```sql
CREATE DATABASE progress_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Create Database User
```sql
CREATE USER 'tracker_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON progress_tracker.* TO 'tracker_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Import Schema
```bash
mysql -u tracker_user -p progress_tracker < database/schema.sql
```

### 4. Update Database Configuration
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'progress_tracker');
define('DB_USER', 'tracker_user');
define('DB_PASS', 'your_secure_password');
```

## 🔐 Security Hardening

### 1. Change Default Credentials
Update the admin user password in the database:
```sql
UPDATE users SET password = '$2y$12$NEW_HASHED_PASSWORD_HERE' WHERE username = 'admin';
```

### 2. SSL/HTTPS Configuration
- Obtain SSL certificate (Let's Encrypt recommended)
- Update `.htaccess` to force HTTPS
- Enable HSTS headers

### 3. Web Server Configuration

#### Apache Configuration
Add to your virtual host:
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /path/to/progress-tracker
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    # Security headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    # Hide server info
    ServerTokens Prod
    ServerSignature Off
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /path/to/progress-tracker;
    index index.php;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-Frame-Options DENY always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /(config|includes|logs)/ {
        deny all;
    }
}
```

## 🚀 Deployment Steps

### 1. Upload Files
```bash
# Using rsync (recommended)
rsync -avz --exclude='.git' --exclude='logs/*' ./ user@server:/path/to/web/directory/

# Or using FTP/SFTP
# Upload all files except .git directory and logs
```

### 2. Set Up Database
```bash
# On the server
mysql -u root -p
# Run the database creation and user setup commands above
# Import the schema
```

### 3. Configure Environment
```bash
# Update configuration files
nano config/config.php
nano config/database.php

# Set file permissions
chmod -R 755 .
chmod 600 config/database.php
chmod 755 logs/
```

### 4. Test Installation
- Visit your domain
- Test user registration
- Test login functionality
- Test task creation and management
- Check error logs for any issues

## 🔍 Monitoring and Maintenance

### 1. Log Monitoring
Monitor these log files:
- `logs/app.log` - Application errors and security events
- Web server error logs
- Database error logs

### 2. Security Monitoring
- Monitor failed login attempts
- Check for unusual activity patterns
- Regular security updates

### 3. Backup Strategy
```bash
# Database backup
mysqldump -u tracker_user -p progress_tracker > backup_$(date +%Y%m%d).sql

# File backup
tar -czf backup_files_$(date +%Y%m%d).tar.gz /path/to/progress-tracker
```

### 4. Performance Optimization
- Enable OPcache
- Configure proper caching headers
- Monitor database performance
- Regular database optimization

## 🛠️ Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials
   - Verify database server is running
   - Check firewall settings

2. **Permission Denied Errors**
   - Verify file permissions
   - Check web server user permissions
   - Ensure logs directory is writable

3. **Session Issues**
   - Check PHP session configuration
   - Verify session directory permissions
   - Check cookie settings for HTTPS

4. **HTTPS Redirect Loop**
   - Check .htaccess HTTPS redirect rules
   - Verify SSL certificate installation
   - Check web server configuration

### Debug Mode
For troubleshooting, temporarily enable debug mode:
```php
// In config/config.php
define('APP_ENV', 'development');
define('APP_DEBUG', true);
```

**Remember to disable debug mode after troubleshooting!**

## 📞 Support

For deployment issues:
1. Check application logs in `logs/app.log`
2. Check web server error logs
3. Verify all configuration settings
4. Test database connectivity
5. Check file permissions

## 🔄 Updates

To update the application:
1. Backup current installation
2. Upload new files
3. Run any database migrations
4. Clear any caches
5. Test functionality

---

**Important**: Always test the deployment in a staging environment before deploying to production!