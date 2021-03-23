FROM craftcms/php-fpm:8.0-dev
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN composer --version

# switch to the root user to install mysql tools
USER root
RUN apk add --no-cache mysql-client

RUN apk --no-cache add shadow && \
usermod -u 1000 www-data && \
groupmod -g 1000 www-data
USER www-data

