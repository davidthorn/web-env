#!/bin/bash

sudo docker-compose -f docker-compose.yml build --force-rm --pull --build-arg USER_ID=$(id -u)
