#!/bin/bash
# Quick Deployment Script for munasiq.org
# Run this script to deploy your application

set -e  # Exit on error

echo "🚀 Starting deployment for munasiq.org..."

# Check if .env.prod exists
if [ ! -f .env.prod ]; then
    echo "❌ Error: .env.prod not found!"
    exit 1
fi
# Build and deploy services
echo "🐳 Building and starting Docker services..."
docker compose --env-file .env.prod -f docker-compose.prod.yml up -d --build



# Wait for services to be healthy
echo "⏳ Waiting for services to be ready..."
sleep 10

# Run migrations
echo "🗄️  Running database migrations..."
docker exec -u www-data laravel_app php artisan migrate --force

# Optimize Laravel
echo "⚡ Optimizing Laravel..."
docker exec -u www-data laravel_app php artisan view:clear
docker exec -u www-data laravel_app php artisan config:cache
docker exec -u www-data laravel_app php artisan route:cache
docker exec -u www-data laravel_app php artisan view:cache
docker exec -u www-data laravel_app php artisan optimize

echo "✅ Deployment complete!"
echo ""
echo "🌐 Access your application at:"
echo "   Main App: localhost:8001"
echo "   PHPMyAdmin: http://localhost:8080"
echo ""
echo "📝 Note: Configure your host Nginx reverse proxy to forward traffic to localhost:8001"
