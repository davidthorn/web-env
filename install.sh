#!/bin/bash

docker-compose build --no-cache

docker-compose up -d

docker exec -it craftcms_webphp composer install

docker exec -it craftcms_webphp php ./craft install/craft --interactive=0 --username=admin --site-url=http://localhost:8082 --site-name="Springlane Magazine" --password=password --language=en --email=backend@springlane.de
