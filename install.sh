#!/bin/bash

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

