#!/bin/bash
echo "Stopping server and database..."
docker compose -f docker-compose.dev.yml down

echo "All services stopped." 