#!/bin/bash
set -e

cd /var/www/html

# Create public/storage symlink if the app uses Laravel's public disk
php artisan storage:link || true

# Cache configuration and views using the environment supplied by Render
php artisan config:cache
php artisan view:cache

exec apache2-foreground
