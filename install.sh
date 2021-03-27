#!/bin/bash

docker-compose build --no-cache

docker-compose up -d

docker exec -it sw_webphp chmod -R 755 custom/plugins
docker exec -it sw_webphp chmod -R 755 engine/Shopware/Plugins/Community
docker exec -it sw_webphp chmod -R 755 files
docker exec -it sw_webphp chmod -R 755 media
docker exec -it sw_webphp chmod -R 755 var
docker exec -it sw_webphp chmod -R 755 web

docker exec -it sw_webphp make init