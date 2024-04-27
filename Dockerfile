FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    libpq-dev \
    supervisor \
    unzip \
    libzip-dev \
    && docker-php-ext-install \
    pdo_pgsql \
    zip \
    sockets

COPY ./ /var/www/html
WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
