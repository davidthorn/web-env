#!/bin/bash

sudo docker-compose down --remove-orphans --rmi=all

sudo rm -rf src/vendor # Remove this folder is exists | executed as root just in case you did a step wrong before and messed up the permissions 
rm -rf src/.env        # Remove the .env file so that a clean install is down
rm -rf src/plugins     # Remove the plugins folder so once again a clean install is carried out.

cp src/.env.example src/.env  

sudo chown $(id -u):$(id -g) src/.env

sudo docker-compose build --build-arg CMS_USER_ID=$(id -u) --no-cache

sudo docker-compose up -d

sudo docker exec -it craftcms_webphp composer install
