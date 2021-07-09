#!/bin/bash

function exec() {
  sudo docker-compose exec php $@
}

sudo docker-compose up --no-build -d

sudo docker-compose exec -T php composer install