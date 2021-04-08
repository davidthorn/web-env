#!/bin/bash

USERNAME=davidthorn
SITE_NAME="Ktichen Stories"
CONTAINER=ks_php-dev
PASSWORD=SecretPassword
EMAIL="david.thorn221278@googlemail.com"
HOST="http://localhost:8020"

docker-compose down \
                --remove-orphans \
                #--rmi=all # DO NOT UNCOMMENT - uncomment if you want a super-clean-install by removing all images.

rm -rf src/.env

cp conf/.env src/.env

docker-compose build --no-cache

docker-compose up -d

docker exec -it $CONTAINER composer install

docker exec -it $CONTAINER php craft setup/security-key
docker exec -it $CONTAINER php craft setup/app-id

docker exec -it $CONTAINER php ./craft install/craft \
                                            --interactive=0 \
                                            --username=$USERNAME \
                                            --site-url=$HOST \
                                            --site-name=$SITE_NAME \
                                            --password=$PASSWORD \
                                            --language=en \
                                            --email=$EMAIL

docker exec -it $CONTAINER composer require craftcms/redactor verbb/super-table
docker exec -it $CONTAINER php craft plugin/install redactor
docker exec -it $CONTAINER php craft plugin/install super-table


docker exec -it $CONTAINER php craft project-config/apply
