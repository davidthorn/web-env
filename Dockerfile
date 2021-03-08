FROM craftcms/php-fpm:8.0

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /app