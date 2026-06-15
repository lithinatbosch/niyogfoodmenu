# Contributing to Kids Menu Planner

Thank you for considering contributing to Kids Menu Planner! This document provides guidelines for contributing to the project.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:
- Clear title and description
- Steps to reproduce
- Expected vs actual behavior
- PHP version, browser, and OS
- Screenshots if applicable

### Suggesting Features

Feature requests are welcome! Please:
- Check existing issues first
- Explain the use case
- Describe the expected behavior
- Consider backward compatibility

### Code Contributions

#### Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/yourusername/kids-menu-planner.git`
3. Create a branch: `git checkout -b feature/your-feature-name`
4. Make your changes
5. Test thoroughly
6. Commit: `git commit -m "Add your feature"`
7. Push: `git push origin feature/your-feature-name`
8. Create a Pull Request

#### Code Standards

**PHP:**
- Follow PSR-12 coding standard
- Use type hints
- Document functions with PHPDoc
- Use prepared statements for database queries
- Always sanitize input and escape output

**JavaScript:**
- Use ES6+ features
- Write clear, self-documenting code
- Add comments for complex logic
- Use meaningful variable names
- Follow existing code style

**CSS:**
- Follow existing naming conventions
- Use CSS variables for colors
- Keep specificity low
- Write mobile-first responsive code
- Comment complex selectors

**SQL:**
- Use meaningful table/column names
- Add indexes for frequently queried columns
- Include foreign key constraints
- Write comments for complex queries

#### Security

- Never commit sensitive data (passwords, keys)
- Always use CSRF tokens for state-changing requests
- Validate all user input
- Escape all output
- Use prepared statements for SQL
- Follow OWASP security guidelines

#### Testing

Before submitting:
- Test on multiple browsers
- Test on mobile devices
- Test all CRUD operations
- Verify PWA functionality
- Check for PHP/JavaScript errors
- Test with sample data

#### Documentation

Update documentation if you:
- Add new features
- Change existing functionality
- Modify database schema
- Update configuration options
- Change API endpoints

### Pull Request Process

1. Update README.md with details of changes
2. Update CHANGELOG.md
3. Ensure all tests pass
4. Request review from maintainers
5. Address review comments
6. Squash commits if requested

### Code Review Criteria

- Code quality and style
- Security considerations
- Performance impact
- Browser compatibility
- Mobile responsiveness
- Documentation completeness

## Development Setup

1. Install PHP 8.0+, MySQL, and Nginx
2. Clone the repository
3. Follow QUICKSTART.md for setup
4. Make changes in a feature branch
5. Test thoroughly before committing

## Areas for Contribution

### High Priority
- Browser compatibility fixes
- Security improvements
- Performance optimizations
- Accessibility enhancements
- PWA improvements

### Medium Priority
- Additional features (see CHANGELOG.md)
- UI/UX improvements
- Code refactoring
- Documentation improvements
- Translation support

### Low Priority
- Theme customization
- Additional color schemes
- Icon improvements
- Animation enhancements

## Questions?

Feel free to open an issue for:
- Clarification on contribution process
- Technical questions
- Feature discussion
- General help

## License

By contributing, you agree that your contributions will be licensed under the same license as the project.

## Thank You!

Every contribution helps make Kids Menu Planner better for parents and children everywhere. Thank you for your time and effort! 🎉
