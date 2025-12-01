#!/bin/bash

# XCrawler Setup Script

echo "🕷️  Setting up XCrawler..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✅ .env created."
else
    echo "✅ .env already exists."
fi

# Create Docker network if it doesn't exist (to avoid errors if FlickrHub isn't running yet)
if [ -z "$(docker network ls | grep xcrawler-hub-network)" ]; then
    echo "🌐 Creating shared network 'xcrawler-hub-network'..."
    docker network create xcrawler-hub-network
    echo "✅ Network created."
else
    echo "✅ Network 'xcrawler-hub-network' already exists."
fi

# Start Docker containers
echo "🚀 Starting Docker containers..."
docker-compose up -d

if [ $? -eq 0 ]; then
    echo ""
    echo "🎉 XCrawler is up and running!"
    echo ""
    echo "📊 Dashboard: http://localhost:8080/flick/dashboard"
    echo "🔧 API:       http://localhost:8080/api"
    echo ""
    echo "👉 Make sure FlickrHub is also running and connected to 'xcrawler-hub-network'."
    echo "   See docs/oauth_setup.md for FlickrHub configuration."
else
    echo "❌ Failed to start Docker containers."
    exit 1
fi
