#!/bin/bash

function exec() {
  sudo docker-compose exec php $@
}

sudo docker-compose up -d
exec composer install
exec composer require doctrine/annotations
exec composer require doctrine/orm
exec composer require doctrine/doctrine-bundle
exec composer require --dev symfony/maker-bundle
exec composer require logger
exec composer require twig
exec composer require --dev symfony/profiler-pack
