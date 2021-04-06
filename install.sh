#!/bin/bash

docker-compose down \
                --remove-orphans \
                --rmi=all # DO NOT UNCOMMENT - uncomment if you want a super-clean-install by removing all images.

rm -rf src/vendor
rm -rf src/.env

cp src/.env.example src/.env

docker-compose build --no-cache

docker-compose up -d

docker exec -it craft_cms_webphp_1 composer install

docker exec -it craft_cms_webphp_1 php craft setup/security-key
docker exec -it craft_cms_webphp_1 php craft setup/app-id

docker exec -it craft_cms_webphp_1 php ./craft install/craft \
                                            --interactive=0 \
                                            --username=admin \
                                            --site-url=http://localhost:8084 \
                                            --site-name="Craft CMS - Redactor" \
                                            --password=password \
                                            --language=en \
                                            --email=david.thorn221278@googlemail.com
