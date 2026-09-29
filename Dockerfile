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

# Environment variables for Composer stability
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_MEMORY_LIMIT=-1

# Install official PHP extension installer helper
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

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
        pdo_sqlite \
        gd \
        zip \
        bcmath \
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

# Copy application source code
COPY . .

# Copy compiled frontend assets from node_builder stage
COPY --from=node_builder /app/public/build ./public/build

# Install Composer dependencies with full codebase present and platform check bypassed
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-reqs

# Set permissions for web server
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose HTTP port (Render dynamically allocates PORT, default 10000 / 80)
EXPOSE 80 10000

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
