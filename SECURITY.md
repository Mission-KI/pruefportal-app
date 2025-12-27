# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 0.x.x   | :white_check_mark: |

## Reporting a Vulnerability

We take security vulnerabilities seriously. If you discover a security issue, please report it responsibly.

**Do not report security vulnerabilities through public GitHub issues.**

### How to Report

Send an email to **phihes@gmail.com** with:

- A description of the vulnerability
- Steps to reproduce the issue
- Potential impact assessment
- Any suggested fixes (optional)

### What to Expect

- **Acknowledgment**: We will acknowledge receipt within 3 business days
- **Assessment**: We will investigate and assess the severity
- **Updates**: We will keep you informed of our progress
- **Resolution**: We aim to resolve critical issues within 30 days
- **Credit**: We will credit reporters in release notes (unless anonymity is preferred)

### Scope

This policy applies to:

- The Prüfportal application code
- Official Docker images
- Configuration and deployment scripts

Out of scope:

- Third-party dependencies (report to upstream maintainers)
- Social engineering attacks
- Physical security

## Security Best Practices

When deploying this application:

1. **Change default credentials** - Never use example passwords in production
2. **Use HTTPS** - Always deploy behind TLS/SSL
3. **Secure environment variables** - Protect `.env` files and secrets
4. **Keep dependencies updated** - Regularly run `composer update` and `npm update`
5. **Review access controls** - Limit database and S3 permissions to minimum required
