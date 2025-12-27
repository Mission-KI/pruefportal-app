#!/bin/bash
set -euo pipefail

# Script to completely reset the database by recreating the Docker volume

echo "⚠️  WARNING: This will completely destroy and recreate the local database!"
echo "All data will be lost. This action cannot be undone."
echo ""
read -p "Are you sure you want to continue? (yes/no): " confirmation

if [ "$confirmation" != "yes" ]; then
    echo "Database reset cancelled."
    exit 0
fi

echo ""
echo "🔄 Starting database reset..."

# Stop containers
echo "📦 Stopping containers..."
docker compose -f docker-compose.dev.yml down

# Remove database volume (correct volume name from docker-compose.dev.yml)
echo "🗑️  Removing database volume..."
docker volume rm mission-ki_mission_ki_postgres_data 2>/dev/null || {
    echo "ℹ️  Database volume not found or already removed."
}

echo "Done resetting the database. Please start the containers again with ./start.sh"
