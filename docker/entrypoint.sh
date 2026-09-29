#!/bin/sh
set -e

# Render assigns dynamic PORT environment variable (defaults to 80 or 10000)
PORT=${PORT:-80}
echo "=> Configuring Nginx to listen on port: $PORT"
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/nginx.conf

# Ensure storage & bootstrap cache directories exist with correct permissions
echo "=> Ensuring storage & bootstrap cache permissions..."
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# If SQLite is configured and file does not exist, initialize it
if [ "$DB_CONNECTION" = "sqlite" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "=> Creating SQLite database file..."
        touch /var/www/html/database/database.sqlite
        chown www-data:www-data /var/www/html/database/database.sqlite
        chmod 664 /var/www/html/database/database.sqlite
    fi
fi

# Ensure storage symlink exists
echo "=> Creating storage symlink..."
php artisan storage:link --force || true

# Run database migrations if requested or if external database is configured
if [ "$AUTO_MIGRATE" = "true" ] || [ -n "$DB_HOST" ]; then
    echo "=> Running database migrations..."
    php artisan migrate --force || echo "=> Warning: Migrations failed or database is not reachable yet. Skipping..."
fi

# Optimize Laravel caching
echo "=> Caching Laravel configuration, routes, and views..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "=> Starting Supervisor (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
