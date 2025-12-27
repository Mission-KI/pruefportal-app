#!/bin/bash
# =============================================================================
# Stop Local Monitoring Stack
# =============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "Stopping monitoring stack..."
docker compose -f docker-compose.local.yml down

echo "Done."
