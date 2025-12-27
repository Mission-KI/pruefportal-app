#!/bin/bash
# =============================================================================
# Start Local Monitoring Stack
# =============================================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "=== Starting Local Monitoring Stack ==="
echo ""

# Fix permissions for Grafana
chmod 644 config/grafana/provisioning/datasources/datasources.yml 2>/dev/null || true

# Start the stack
docker compose -f docker-compose.local.yml up -d

echo ""
echo "=== Waiting for services ==="
sleep 3

# Check status
docker compose -f docker-compose.local.yml ps

echo ""
echo "=== Checking Loki ==="
curl -s http://localhost:3100/ready && echo " - Loki is ready" || echo " - Loki not ready yet (wait a moment)"

echo ""
echo "=== Access ==="
echo "Grafana:  http://localhost:3001"
echo "  - Login: admin / admin"
echo "  - Go to: Explore > Select 'Loki' datasource"
echo ""
echo "Test Endpoints (generate logs):"
echo "  curl http://localhost:8070/log-test/info      # Info log (200)"
echo "  curl http://localhost:8070/log-test/warning   # Warning log (200)"
echo "  curl http://localhost:8070/log-test/error     # Error log (500)"
echo "  curl http://localhost:8070/log-test/exception # Exception (500)"
echo ""
echo "Query in Grafana:"
echo "  {job=\"cakephp\"}              # All CakePHP logs"
echo "  {job=\"cakephp\", level=\"error\"} # Only errors"
