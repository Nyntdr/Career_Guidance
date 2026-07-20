#!/bin/sh
set -e

cd /var/www/html

# Fix permissions on the mounted volume (named volumes reset ownership to root on mount)
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    php artisan key:generate --force --no-interaction
fi

echo "Waiting for MySQL..."
attempt=0
max_attempts=30

until php -r "
try {
    new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    attempt=$((attempt + 1))

    if [ "$attempt" -ge "$max_attempts" ]; then
        echo "Could not connect to MySQL after ${max_attempts} attempts."
        exit 1
    fi

    sleep 2
done

php artisan migrate --force --no-interaction
php artisan storage:link --force --no-interaction 2>/dev/null || true

if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
fi

exec "$@"
