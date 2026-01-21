# Contributing to Multi-User Progress Tracker

Thank you for your interest in contributing to this project! This guide will help you get started.

## 🚀 Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/yourusername/progress-tracker.git
   cd progress-tracker
   ```
3. **Set up the development environment** following the README instructions
4. **Create a new branch** for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

## 📝 Development Guidelines

### Code Style
- Follow PSR-12 coding standards for PHP
- Use meaningful variable and function names
- Add comments for complex logic
- Keep functions small and focused

### Database Changes
- Always create migration scripts for schema changes
- Test migrations on sample data
- Document any new database requirements

### Security
- Never commit sensitive data (passwords, API keys)
- Use prepared statements for all database queries
- Validate and sanitize all user inputs
- Follow OWASP security guidelines

## 🧪 Testing

Before submitting your changes:
1. Test all functionality manually
2. Verify database operations work correctly
3. Check responsive design on different screen sizes
4. Ensure no PHP errors or warnings

## 📤 Submitting Changes

1. **Commit your changes** with descriptive messages:
   ```bash
   git add .
   git commit -m "Add feature: description of what you added"
   ```

2. **Push to your fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

3. **Create a Pull Request** on GitHub with:
   - Clear description of changes
   - Screenshots if UI changes are involved
   - Any breaking changes noted

## 🐛 Bug Reports

When reporting bugs, please include:
- Steps to reproduce the issue
- Expected vs actual behavior
- PHP and MySQL versions
- Browser information (if frontend issue)
- Any error messages or logs

## 💡 Feature Requests

For new features:
- Describe the use case and benefit
- Consider backward compatibility
- Discuss implementation approach
- Check if similar features exist

## 📋 Areas for Contribution

- **Security improvements**
- **Performance optimizations**
- **UI/UX enhancements**
- **Additional task management features**
- **Mobile app development**
- **API documentation**
- **Unit tests**
- **Accessibility improvements**

## 🤝 Code of Conduct

- Be respectful and inclusive
- Focus on constructive feedback
- Help others learn and grow
- Maintain a positive environment

## 📞 Questions?

Feel free to open an issue for any questions about contributing!

Thank you for helping make this project better! 🎉