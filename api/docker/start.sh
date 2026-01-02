#!/bin/bash
set -e

# Run migrations
php /var/www/html/artisan migrate --force

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    php /var/www/html/artisan key:generate --force
fi

# Clear and cache configs
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
