#!/bin/bash

set -e

echo "🚀 Starting Laravel deployment optimization..."

# Clear all caches first
echo "📦 Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Optimize for production
echo "⚡ Optimizing application..."
php artisan optimize

# Cache configuration
echo "📝 Caching configuration..."
php artisan config:cache

# Cache routes
echo "🛣️  Caching routes..."
php artisan route:cache

# Cache views
echo "👁️  Caching views..."
php artisan view:cache

# Cache events
echo "🎯 Caching events..."
php artisan event:cache

# Clear and rebuild optimized autoloader
echo "🔄 Rebuilding autoloader..."
composer dump-autoload --optimize --classmap-authoritative

echo "✅ Deployment optimization complete!"

