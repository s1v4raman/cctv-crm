#!/bin/sh
set -e

# Render assigns dynamic PORT environment variable (defaults to 80 or 10000)
PORT=${PORT:-80}
echo "=> Configuring Nginx to listen on port: $PORT"
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/nginx.conf

# Ensure PHP-FPM does not clear environment variables for worker processes
mkdir -p /usr/local/etc/php-fpm.d
echo "clear_env = no" > /usr/local/etc/php-fpm.d/zz-env.conf

# Ensure storage & bootstrap cache directories exist
echo "=> Ensuring storage & bootstrap cache directories..."
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

# Guarantee .env file exists
if [ ! -f /var/www/html/.env ]; then
    echo "=> Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env 2>/dev/null || touch /var/www/html/.env
fi

# Ensure valid base64 APP_KEY
if [ -z "$APP_KEY" ] || ! echo "$APP_KEY" | grep -q "base64:"; then
    echo "=> Ensuring valid base64 application key..."
    export APP_KEY=$(php artisan key:generate --show)
fi

# Smart Database fallback: If DB_HOST is not set or set to local/empty, fall back to SQLite
if [ -z "$DB_HOST" ] || [ "$DB_HOST" = "127.0.0.1" ] || [ "$DB_HOST" = "localhost" ]; then
    echo "=> DB_HOST is not set. Defaulting to local SQLite database..."
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/html/database/database.sqlite
    touch /var/www/html/database/database.sqlite
    chmod 666 /var/www/html/database/database.sqlite
    chmod 777 /var/www/html/database
fi

# Discover Laravel packages
echo "=> Discovering Laravel packages..."
php artisan package:discover --ansi || true

# Ensure storage symlink exists
echo "=> Creating storage symlink..."
php artisan storage:link --force || true

# Flush any stale caches so fresh runtime variables are read
echo "=> Clearing stale bootstrap and config caches..."
php artisan optimize:clear || true
php artisan config:clear || true
php artisan view:clear || true
php artisan route:clear || true
php artisan cache:clear || true

# Run database migrations
echo "=> Running database migrations..."
php artisan migrate --force || echo "=> Warning: Migrations failed. Check database configuration."

# Guarantee default Admin, Reviewer, and Demo user accounts exist
echo "=> Ensuring Super Admin, Reviewer, and Staff accounts exist..."
php artisan tinker --execute="
try {
    \App\Models\User::updateOrCreate(
        ['email' => 'admin@cctvcrm.com'],
        [
            'name' => 'Suresh Prabhu (Admin)',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]
    );
    \App\Models\User::updateOrCreate(
        ['email' => 'test@example.com'],
        [
            'name' => 'Admin User',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]
    );
    \App\Models\User::updateOrCreate(
        ['email' => 'reviewer@cctvcrm.com'],
        [
            'name' => 'Project Reviewer & Analyst',
            'password' => bcrypt('reviewer123'),
            'role' => 'staff',
            'email_verified_at' => now(),
        ]
    );
    \App\Models\User::updateOrCreate(
        ['email' => 'bob@example.com'],
        [
            'name' => 'Bob Technician',
            'password' => bcrypt('password'),
            'role' => 'technician',
            'email_verified_at' => now(),
        ]
    );
    \App\Models\User::updateOrCreate(
        ['email' => 'customer@cctvcrm.com'],
        [
            'name' => 'John Customer',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]
    );
    echo '=> Admin, Reviewer, and Demo accounts verified successfully.' . PHP_EOL;
} catch (\Throwable \$e) {
    echo '=> Account creation note: ' . \$e->getMessage() . PHP_EOL;
}
" || true

# Seed database with catalog products & full demo data if seeder exists
echo "=> Running database seeders..."
php artisan db:seed --force || echo "=> Seeders finished or already run."

# Cache routes and views for production performance
echo "=> Caching routes and views..."
php artisan route:cache || true
php artisan view:cache || true

# CRITICAL: Grant complete ownership to www-data right before starting supervisor
# This ensures PHP-FPM can write sessions, SQLite locks, logs, and compiled views.
echo "=> Granting full ownership to www-data for web execution..."
chown -R www-data:www-data /var/www/html/storage \
                           /var/www/html/bootstrap/cache \
                           /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod 666 /var/www/html/database/database.sqlite* 2>/dev/null || true
chmod 777 /var/www/html/database

echo "=> Starting Supervisor (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
