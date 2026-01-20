#!/bin/bash

# ============================================
# Production Deployment Script
# ============================================
# Run this script on your production server after pulling the latest code.
# Usage: ./scripts/deploy.sh

set -e

echo "🚀 Starting deployment..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ .env file not found. Please create it from .env.example"
    exit 1
fi

# Install PHP dependencies (no dev packages in production)
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies and build assets
echo "📦 Installing Node dependencies..."
npm ci --production=false

echo "🔨 Building assets..."
npm run build

# Clear and cache configuration
echo "⚙️ Optimizing configuration..."
php artisan config:clear
php artisan config:cache

# Clear and cache routes
echo "🛣️ Optimizing routes..."
php artisan route:clear
php artisan route:cache

# Clear and cache views
echo "👁️ Optimizing views..."
php artisan view:clear
php artisan view:cache

# Run database migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Clear application cache
echo "🧹 Clearing cache..."
php artisan cache:clear

# Restart queue workers (if using supervisor)
echo "👷 Restarting queue workers..."
php artisan queue:restart

# Create storage link if not exists
if [ ! -L public/storage ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link
fi

# Set proper permissions
echo "🔒 Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Filament assets
echo "🎨 Publishing Filament assets..."
php artisan filament:upgrade

echo "✅ Deployment completed successfully!"
echo ""
echo "Post-deployment checklist:"
echo "  1. Check that the site is working"
echo "  2. Verify queue workers are running"
echo "  3. Check error logs for any issues"
echo "  4. Test critical user flows"
