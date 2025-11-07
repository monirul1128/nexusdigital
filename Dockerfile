# Multi-stage build for Laravel (PHP-FPM) and frontend assets

# 1) Base PHP image with extensions
FROM php:8.2-fpm-alpine AS php-base

# Install nginx in final stage later
RUN apk add --no-cache nginx

# Install system dependencies and PHP extensions commonly needed by Laravel
RUN apk add --no-cache \
    icu-dev libzip-dev oniguruma-dev \
    git curl bash shadow libpng-dev libjpeg-turbo-dev freetype-dev \
    mysql-client \
    nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd intl zip

# Copy composer from official image to avoid installing PHP again
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 2) Install PHP dependencies (composer) with cache
FROM php-base AS vendor
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-progress --optimize-autoloader

# 3) Build frontend assets (Vite) with Node
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci || npm install
COPY resources ./resources
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build || npm run build --if-present

# 4) Final runtime image
FROM php-base AS app

# Ensure nginx is available and configure directories
RUN mkdir -p /run/nginx /var/log/nginx

# Set production environment
ENV APP_ENV=production \
    APP_DEBUG=false \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

# Create system user for running the app
RUN addgroup -S www && adduser -S www -G www

WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy Composer vendor folder from vendor stage
COPY --from=vendor /var/www/html/vendor /var/www/html/vendor

# Copy built assets from frontend stage (assumes Vite outputs to public/build)
COPY --from=frontend /app/public/build /var/www/html/public/build

# Copy nginx config and entrypoint
COPY deploy/nginx/nginx.conf /etc/nginx/nginx.conf
COPY deploy/nginx/nexusdigital.conf /etc/nginx/conf.d/default.conf
COPY deploy/nginx/nexusdigital.conf /etc/nginx/conf.d/default.template

# Optimize Laravel
RUN php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache || true

# Fix permissions
RUN chown -R www:www /var/www/html \
 && find storage -type d -exec chmod 775 {} \; \
 && find storage -type f -exec chmod 664 {} \; \
 && chmod -R ug+rwx storage bootstrap/cache

# Switch back to root to run both nginx and php-fpm
USER root

# Render provides PORT; nginx must bind to it. We'll export it into nginx config at runtime.
ENV PORT=8080

EXPOSE 8080

# Entrypoint to run php-fpm and nginx together
COPY --chown=www:www . /var/www/html

# Start script
RUN printf '#!/bin/sh\nset -e\n: "${PORT:=8080}"\nsed -e "s/${PORT}/${PORT}/g" /etc/nginx/conf.d/default.template > /etc/nginx/conf.d/default.conf\nphp-fpm -D\nexec nginx -c /etc/nginx/nginx.conf -g "daemon off;"\n' > /usr/local/bin/start && \
    chmod +x /usr/local/bin/start

CMD ["/usr/local/bin/start"]
