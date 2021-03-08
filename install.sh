#!/bin/bash

docker-compose up --build -d

docker exec craft_cms_webphp composer install

docker exec -it craft_cms_webphp php craft setup

