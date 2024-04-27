#!/usr/bin/env bash

set -e

role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}

if [ "$env" != "local" ]; then
    echo "Caching configuration..."
    (cd /var/www/html && php artisan config:cache && php artisan route:cache && php artisan view:cache)
fi

echo "Running for \"$role\""

if [ "$role" = "app" ]; then
    echo "Running php-fpm"
    exec php-fpm
elif [ "$role" = "queue" ]; then
    echo "Running supervisord"
    exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
elif [ "$role" = "scheduler" ]; then
    echo "Running schedule"
    while [ true ]
    do
      php /var/www/html/artisan schedule:run --verbose --no-interaction
      sleep 60
    done
else
    echo "Could not match the container role \"$role\""
    exit 1
fi
