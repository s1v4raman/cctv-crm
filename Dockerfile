# ==========================================
# Stage 1: Build Frontend Assets with Vite
# ==========================================
FROM node:20-alpine AS node_builder

WORKDIR /app

# Copy dependency specifications
COPY package*.json ./

# Install npm dependencies (resilient to lockfile drift)
RUN npm ci || npm install

# Copy application source code for Vite & Tailwind scanning
COPY . .

# Build compiled assets into public/build
RUN npm run build

# ==========================================
# Stage 2: Production PHP 8.3 + Nginx Stack
# ==========================================
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Install official PHP extension installer helper
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install system utilities, web server, and PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    bash \
    && install-php-extensions \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        bcmath \
        intl \
        opcache \
        pcntl

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Remove default nginx configurations to avoid port conflicts
RUN rm -rf /etc/nginx/http.d/* /etc/nginx/conf.d/*

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Normalize line endings and grant execute permissions to entrypoint
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    sed -i 's/\r$//' /usr/local/bin/entrypoint.sh

# Copy composer manifests first for layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies without generating autoloader or running scripts
RUN composer install --no-dev --no-interaction --prefer-dist --no-autoloader --no-scripts

# Copy application source code
COPY . .

# Copy compiled frontend assets from node_builder stage
COPY --from=node_builder /app/public/build ./public/build

# Finish Composer classmap autoload optimization (strictly no artisan scripts at build time)
RUN composer dump-autoload --optimize --no-dev --no-scripts

# Set permissions for web server
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose HTTP port (Render dynamically allocates PORT, default 10000 / 80)
EXPOSE 80 10000

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
