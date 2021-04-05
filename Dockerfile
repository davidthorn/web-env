FROM php:8.0-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update -y && apt-get install -y git libzip-dev zip && \
    docker-php-ext-install zip

RUN usermod -u 1000 www-data & groupmod -g 1000 www-data

RUN chown www-data:www-data /var/www

USER www-data

WORKDIR /app
