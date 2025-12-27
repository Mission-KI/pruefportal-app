#!/bin/bash
set -euo pipefail

# Parse command line arguments
SKIP_SEED=false
while [[ $# -gt 0 ]]; do
  case $1 in
    --no-seed)
      SKIP_SEED=true
      shift
      ;;
    *)
      echo "Unknown option $1"
      exit 1
      ;;
  esac
done

echo "Starting the database and the backend server with docker..."

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        echo "Creating .env from .env.example..."
        cp .env.example .env
        echo ".env file created."
    else
        echo "Warning: No .env or .env.example file found!"
    fi
fi

docker compose -f docker-compose.dev.yml up -d

# Wait until the Postgres container is up und running
DB_CONTAINER="mission-ki-db"
echo "Waiting for database container ($DB_CONTAINER) to become healthy..."
for i in {1..60}; do
  STATUS=$(docker inspect -f '{{.State.Health.Status}}' "$DB_CONTAINER" 2>/dev/null || echo "unknown")
  if [ "$STATUS" = "healthy" ]; then
    echo "Database is healthy."
    break
  fi
  if [ "$STATUS" = "unhealthy" ]; then
    echo "Database container reported unhealthy status." >&2
    exit 1
  fi
  sleep 1
  if [ "$i" -eq 60 ]; then
    echo "Timed out waiting for database health check." >&2
    exit 1
  fi
done

echo "Ensuring composer dependencies are installed and up-to-date..."
docker compose -f docker-compose.dev.yml exec -T -w /app server sh -lc 'composer install --no-interaction --prefer-dist'

# Run database migrations
echo "Running database migrations..."
# Check migration status and capture output
MIGRATION_STATUS=$(docker compose -f docker-compose.dev.yml exec -T -w /app server sh -lc 'composer db:migrate:status' 2>/dev/null || echo "FAILED")

if echo "$MIGRATION_STATUS" | grep -q "down"; then
  echo "Pending migrations detected. Running migrations..."
  docker compose -f docker-compose.dev.yml exec -T -w /app server sh -lc 'composer db:migrate'
  echo "Migrations completed."
elif [ "$MIGRATION_STATUS" = "FAILED" ]; then
  echo "Migration status check failed. Initializing database..."
  docker compose -f docker-compose.dev.yml exec -T -w /app server sh -lc 'composer db:setup'
  echo "Database setup completed."
else
  echo "All migrations are up to date."
fi

# Run database seeds (optional)
if [ "$SKIP_SEED" = "false" ]; then
  echo "Running database seeds..."
  docker compose -f docker-compose.dev.yml exec -T -w /app server sh -lc 'composer db:seed'
  echo "Database seeding completed."
else
  echo "Skipping database seeding (--no-seed flag provided)."
fi

echo "All services started!"
echo "PHP server running at: http://localhost:8070"
echo "Database running on port: 5642"

# Show Mailpit URL only when using SMTP driver (dev mode with local email testing)
EMAIL_DRIVER=$(docker compose -f docker-compose.dev.yml exec -T server printenv EMAIL_DRIVER 2>/dev/null || echo "")
if [ "$EMAIL_DRIVER" = "smtp" ]; then
  echo "Mailpit UI running at: http://localhost:8025"
fi
