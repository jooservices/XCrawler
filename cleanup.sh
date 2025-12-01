#!/bin/bash

# XCrawler Cleanup Script

echo "🧹 Cleaning up XCrawler..."

# Stop containers
echo "🛑 Stopping containers..."
docker-compose down

# Ask if user wants to remove volumes
read -p "❓ Do you want to remove persistent data (volumes)? [y/N] " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🗑️  Removing volumes..."
    docker-compose down -v
    echo "✅ Volumes removed."
else
    echo "ℹ️  Volumes kept."
fi

# Ask if user wants to remove the network
read -p "❓ Do you want to remove the shared network 'xcrawler-hub-network'? [y/N] " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🌐 Removing network..."
    docker network rm xcrawler-hub-network 2>/dev/null || echo "⚠️  Network might be in use or already removed."
    echo "✅ Network removal attempted."
else
    echo "ℹ️  Network kept."
fi

echo "✨ Cleanup complete."
