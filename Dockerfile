FROM php:8.0-fpm
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN composer --version

USER root

RUN apt-get -y update \
    && apt-get install -y libicu-dev libfreetype6-dev\
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl opcache

RUN apt-get install -y \
        libpng-dev \
        libzip-dev \
        libjpeg-dev \
        zip 

RUN apt-get install -y advancecomp \
    gifsicle jhead jpegoptim \
    libjpeg-progs optipng \
    pngcrush pngquant guetzli


RUN docker-php-ext-configure gd \
    --with-jpeg \
    --with-freetype

RUN docker-php-ext-install gd pdo pdo_mysql mysqli zip

ADD --chown=www-data:www-data ./shopware /app
VOLUME [ "/app" ]

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data 
RUN chown -R www-data:www-data /app 

USER www-data
WORKDIR /app
