#!/bin/sh
set -e

# Render assigns dynamic PORT environment variable (defaults to 80 or 10000)
PORT=${PORT:-80}
echo "=> Configuring Nginx to listen on port: $PORT"
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/nginx.conf

# Ensure PHP-FPM does not clear environment variables for worker processes
mkdir -p /usr/local/etc/php-fpm.d
echo "clear_env = no" > /usr/local/etc/php-fpm.d/zz-env.conf

# Ensure storage & bootstrap cache directories exist with correct permissions
echo "=> Ensuring storage & bootstrap cache directories and permissions..."
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Auto-generate APP_KEY if missing
if [ -z "$APP_KEY" ]; then
    echo "=> APP_KEY not provided. Generating a production application key..."
    export APP_KEY=$(php artisan key:generate --show)
fi

# Smart Database fallback: If DB_HOST is not set or set to local/empty, fall back to SQLite
if [ -z "$DB_HOST" ] || [ "$DB_HOST" = "127.0.0.1" ] || [ "$DB_HOST" = "localhost" ]; then
    echo "=> DB_HOST is not set. Defaulting to local SQLite database..."
    export DB_CONNECTION=sqlite
    export DB_DATABASE=/var/www/html/database/database.sqlite
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
    chmod 664 /var/www/html/database/database.sqlite
fi

# Discover Laravel packages
echo "=> Discovering Laravel packages..."
php artisan package:discover --ansi || true

# Ensure storage symlink exists
echo "=> Creating storage symlink..."
php artisan storage:link --force || true

# Clear cached config first so runtime environment variables are read
php artisan config:clear || true

# Run database migrations
echo "=> Running database migrations..."
php artisan migrate --force || echo "=> Warning: Migrations failed. Check database configuration."

# Guarantee default Admin and Demo user accounts exist
echo "=> Ensuring Super Admin and Staff accounts exist..."
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
    \App\Models\User::updateOrCreate(
        ['email' => 'reviewer@cctvcrm.com'],
        [
            'name' => 'Project Reviewer & Analyst',
            'password' => bcrypt('reviewer123'),
            'role' => 'staff',
            'email_verified_at' => now(),
        ]
    );
    echo '=> Admin, Customer, and Reviewer accounts created and verified successfully.' . PHP_EOL;
} catch (\Throwable \$e) {
    echo '=> Account creation error: ' . \$e->getMessage() . PHP_EOL;
}
" || true

# Seed database with catalog products & full demo data if seeder exists
echo "=> Running database seeders..."
php artisan db:seed --force || echo "=> Seeders finished or already run."

# Flush any previous caches to guarantee latest views and designs compile freshly
echo "=> Flushing stale view, route, and config caches..."
php artisan optimize:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# Cache Laravel configuration, routes, and views for production performance
echo "=> Caching fresh Laravel configuration, routes, and views..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "=> Starting Supervisor (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
