FROM php:8.0-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update

RUN apt-get install -y \
        libzip-dev \
        zip 
RUN docker-php-ext-install pdo pdo_mysql mysqli zip

RUN composer create-project symfony/website-skeleton

RUN mv website-skeleton app

WORKDIR /app