#!/bin/bash

sudo docker-compose -f docker-compose.yml build --force-rm -q --pull --build-arg USER_ID=$(id -u)
