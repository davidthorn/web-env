#!/bin/bash

docker-compose down \
                --remove-orphans \
                #--rmi=all # DO NOT UNCOMMENT - uncomment if you want a super-clean-install by removing all images.

rm -rf src/vendor
rm -rf src/.env
rm -rf src/plugins

cp src/.env.example src/.env

if [ ! -d "src/plugins" ]; then
    mkdir src/plugins
fi

if [ ! -d "src/plugins/basic-plugin" ]; then
    git clone git@deploy.office.springlane.de:backend-modules/base-craft-plugin.git --branch=development src/plugins/basic-plugin
    cd src
    composer require springlane/basic-plugin
    cd ..
fi

docker-compose build --no-cache

docker-compose up -d

docker exec -it craftcms_webphp composer install

docker exec -it craftcms_webphp php craft setup/security-key
docker exec -it craftcms_webphp php craft setup/app-id

docker exec -it craftcms_webphp php ./craft install/craft \
                                            --interactive=0 \
                                            --username=admin \
                                            --site-url=http://localhost:8081 \
                                            --site-name="Springlane Magazine" \
                                            --password=password \
                                            --language=en \
                                            --email=backend@springlane.de

docker exec -it craftcms_webphp php craft plugin/install spl-custom-plugin-handle