#!/bin/bash

function exec() {
  sudo docker-compose exec php $@
}

exec composer require symfony/requirements-checker

exec composer dump-env prod

exec composer install --no-dev --optimize-autoloader

exec bin/console cache:clear