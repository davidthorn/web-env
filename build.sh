#!/bin/bash

USERNAME=davidthorn
SITE_NAME="Ktichen Stories"
CONTAINER=ks_php-dev
PASSWORD=SecretPassword
EMAIL="david.thorn221278@googlemail.com"
HOST="http://localhost:8020"

cp conf/.env src/.env

chown www-data:www-data src/.env
chmod 755 src/.env

docker-compose down \
                --remove-orphans \
                #--rmi=all # DO NOT UNCOMMENT - uncomment if you want a super-clean-install by removing all images.

docker-compose build --no-cache

docker-compose up -d

docker exec -it $CONTAINER composer install
