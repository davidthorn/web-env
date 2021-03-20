FROM php:8.0-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install pdo pdo_mysql mysqli
