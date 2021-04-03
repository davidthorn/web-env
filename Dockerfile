FROM craftcms/php-fpm:8.0-dev
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN composer --version

USER root
RUN apk --no-cache add curl
RUN apk add --no-cache autoconf gcc g++ imagemagick-dev libtool make

# install imagick
# use github version for now until release from https://pecl.php.net/get/imagick is ready for PHP 8
RUN mkdir -p /usr/src/php/ext/imagick; \
    curl -fsSL https://github.com/Imagick/imagick/archive/06116aa24b76edaf6b1693198f79e6c295eda8a9.tar.gz | tar xvz -C "/usr/src/php/ext/imagick" --strip 1; \
    docker-php-ext-install imagick;

RUN apk --no-cache update \
    && apk --no-cache upgrade \
    && apk add --no-cache $PHPIZE_DEPS \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) gd

# switch to the root user to install mysql tools
USER root
RUN apk add --no-cache mysql-client

RUN apk --no-cache add shadow && \
usermod -u 1000 www-data && \
groupmod -g 1000 www-data
USER www-data
