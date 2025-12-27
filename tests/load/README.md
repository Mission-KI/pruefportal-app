# Registration Load Test

Simulates concurrent user registrations to validate system capacity.

## Installation

```bash
npm install
```

## Usage

```bash
# Local testing (5 users)
npm run test:local

# Staging with 10 users
npm run test:staging

# Full load test (50 users)
npm run test:full

# Custom configuration
node registration-load-test.js --env=test --users=25
```

## Cleanup

After testing, review results and run the generated cleanup SQL:

```bash
psql -h localhost -p 5642 -U postgres -d pruefportal < results/cleanup-users-{timestamp}.sql
```
