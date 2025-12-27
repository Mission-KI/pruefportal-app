# Contributing to Prüfportal

Thank you for your interest in contributing to the MISSION KI Prüfportal!

## Getting Started

1. Fork the repository
2. Clone your fork locally
3. Set up the development environment:
   ```bash
   cd backend
   cp .env.example .env
   make start
   ```
4. Create a feature branch: `git checkout -b feat/your-feature`

## Development Workflow

### Code Style

**PHP:**
```bash
composer cs-check    # Check code style
composer cs-fix      # Auto-fix issues
```

**Frontend:**
```bash
npm run css-lint     # Lint SCSS files
npm run build        # Build assets
```

### Running Tests

```bash
composer test        # PHP unit tests
npm test             # Frontend tests
```

### Static Analysis

```bash
# PHPStan (level 8)
vendor/bin/phpstan analyse

# Psalm (level 2)
vendor/bin/psalm
```

## Pull Request Process

1. Ensure all tests pass locally
2. Run code style checks and fix any issues
3. Update documentation if needed
4. Create a pull request with a clear description
5. Link any related issues

### Commit Messages

Use conventional commit format:
- `feat:` New features
- `fix:` Bug fixes
- `docs:` Documentation changes
- `chore:` Maintenance tasks
- `test:` Test additions/changes
- `refactor:` Code refactoring

Example: `feat: add email notification for project approval`

## Reporting Issues

When reporting bugs, please include:
- Steps to reproduce
- Expected behavior
- Actual behavior
- Browser/environment details (if applicable)

## Questions?

Open a discussion or issue on GitHub.
