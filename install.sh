#!/bin/bash

sudo docker-compose down \
                --remove-orphans \
                #--rmi=all # DO NOT UNCOMMENT - uncomment if you want a super-clean-install by removing all images.

rm -rf src/vendor
rm -rf src/.env
rm -rf src/plugins

cp src/.env.example src/.env

sudo docker-compose build --no-cache

sudo docker-compose up -d

sudo docker exec -it craftcms_webphp composer install

sudo docker exec -it craftcms_webphp php craft setup/security-key
sudo docker exec -it craftcms_webphp php craft setup/app-id

sudo docker exec -it craftcms_webphp php ./craft install/craft \
                                            --interactive=0 \
                                            --username=admin \
                                            --site-url=http://localhost:8081 \
                                            --site-name="Springlane Magazine" \
                                            --password=password \
                                            --language=en \
                                            --email=backend@springlane.de

