FROM php:8.0-fpm

ARG USER_ID

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update -y && apt-get install -y git libzip-dev zip libicu-dev && \
    docker-php-ext-configure intl && \
    docker-php-ext-install zip opcache pdo pdo_mysql mysqli intl

RUN usermod -u $USER_ID www-data & groupmod -g $USER_ID www-data

RUN chown www-data:www-data /var/www

RUN mkdir -p /app
VOLUME ["/app"]

RUN chown -R www-data:www-data /app

USER www-data

WORKDIR /app