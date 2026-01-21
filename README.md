# 📋 Multi-User Progress Tracker

A collaborative web-based task management system that allows multiple users to work together on shared tasks while tracking individual progress. Perfect for team projects, academic assignments, or collaborative work environments.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white)

## ✨ Features

### 🔐 **Secure Authentication System**
- Session-based login with secure password hashing
- Rate limiting to prevent brute force attacks
- CSRF protection on all forms
- Automatic session timeout for security

### 📊 **Collaborative Progress Tracking**
- **Individual Progress**: Each user tracks their own completion status
- **Team Overview**: Visual comparison of progress between users
- **Real-time Updates**: Dynamic progress bars and statistics
- **Completion Analytics**: Track team performance and task completion rates

### ✅ **Advanced Task Management**
- **Shared Tasks**: Tasks visible and accessible to all team members
- **Task Creation**: Rich task creation with titles and descriptions
- **Progress Notes**: Add personal notes to task completions
- **Task History**: Track who created tasks and when they were completed

### 🎨 **Modern User Interface**
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile
- **Clean UI**: Professional interface suitable for academic or business use
- **Smooth Animations**: Enhanced user experience with CSS transitions
- **Intuitive Navigation**: Easy-to-use interface with clear visual hierarchy

## 🛠 Technology Stack

- **Backend**: PHP 7.4+ with PDO for database operations
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Database**: MySQL 5.7+ with normalized schema design
- **Security**: Prepared statements, input validation, CSRF protection
- **Architecture**: MVC-like structure with separated concerns

## 📁 Project Structure

```
progress-tracker/
├── 📂 api/                 # REST API endpoints
│   ├── create_task.php
│   ├── get_tasks.php
│   └── update_task.php
├── 📂 assets/              # Static assets
│   ├── css/style.css
│   └── js/script.js
├── 📂 config/              # Configuration files
│   ├── config.php
│   ├── database.php
│   └── database.sample.php
├── 📂 database/            # Database schema and migrations
│   ├── schema.sql
│   └── migrate_to_shared_tasks.sql
├── 📂 includes/            # Core functionality
│   ├── auth.php
│   └── functions.php
├── 📂 pages/               # Application pages
│   └── dashboard.php
├── 📂 error_pages/         # Custom error pages
├── 📂 logs/                # Application logs
├── index.php               # Login page
├── register.php            # User registration
├── logout.php              # Logout functionality
└── setup_production.php    # Production setup script
```

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or local development environment

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/progress-tracker.git
   cd progress-tracker
   ```

2. **Database Setup**
   ```sql
   CREATE DATABASE progress_tracker;
   ```
   Then import the schema:
   ```bash
   mysql -u your_username -p progress_tracker < database/schema.sql
   ```

3. **Configuration**
   ```bash
   cp config/database.sample.php config/database.php
   ```
   Edit `config/database.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'progress_tracker');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Set Permissions**
   ```bash
   chmod 755 logs/
   chmod 644 config/database.php
   ```

5. **Access the Application**
   - Navigate to `http://localhost/progress-tracker/`
   - Default login: `admin` / `admin123`
   - **⚠️ Change the default password immediately!**

## 📖 Usage Guide

### Getting Started
1. **Login** with the default admin credentials
2. **Create your first shared task** using the task creation form
3. **Mark tasks as completed** to track your progress
4. **Register additional users** to see collaborative features
5. **Compare progress** between team members in real-time

### Key Features
- **Task Creation**: Add tasks with detailed descriptions
- **Progress Tracking**: Visual progress bars show completion status
- **Team Collaboration**: See other users' progress on shared tasks
- **Notes**: Add personal notes when completing tasks
- **Statistics**: View team performance and completion rates

## 🔒 Security Features

- **Password Security**: Bcrypt hashing with salt
- **SQL Injection Protection**: Prepared statements throughout
- **XSS Prevention**: Input sanitization and output encoding
- **CSRF Protection**: Token-based form protection
- **Session Security**: Secure session configuration
- **Rate Limiting**: Brute force attack prevention

## 🎓 Academic Value

This project demonstrates:
- **Full-Stack Development**: Complete web application with frontend and backend
- **Database Design**: Normalized schema with proper relationships
- **Security Best Practices**: Industry-standard security implementations
- **User Experience**: Modern, responsive interface design
- **Code Organization**: Clean, maintainable code structure
- **Documentation**: Comprehensive project documentation

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🆘 Support

If you encounter any issues:
1. Check the [Issues](https://github.com/yourusername/progress-tracker/issues) page
2. Run the setup script: `setup_production.php`
3. Check the logs in the `logs/` directory
4. Ensure your PHP and MySQL versions meet requirements

## 🙏 Acknowledgments

- Built with modern web development best practices
- Designed for educational and collaborative purposes
- Inspired by the need for simple, effective team task management

---

**⭐ Star this repository if you find it helpful!**