#!/bin/bash

sudo docker-compose -f docker-compose.yml build --no-cache --pull --build-arg USER_ID=$(id -u)
