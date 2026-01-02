FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    supervisor \
    nginx

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for caching (from api subdirectory)
COPY api/composer.json api/composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-dev

# Copy application code (from api subdirectory)
COPY api/ .

# Generate autoloader and run post-install scripts
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configure nginx
RUN rm /etc/nginx/sites-enabled/default
COPY api/docker/nginx.conf /etc/nginx/sites-enabled/default

# Configure supervisord
COPY api/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy startup script
COPY api/docker/start.sh /start.sh
RUN chmod +x /start.sh

# Expose single port (nginx proxies WebSocket to Reverb internally)
EXPOSE 8000

# Run startup script (migrations + supervisord)
CMD ["/start.sh"]
