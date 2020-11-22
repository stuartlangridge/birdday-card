#!/bin/bash

if [ "$1" == "rebuild" ]; then
    docker rm --force birdday-card-running
    docker build -t birdday-card .
    docker run --publish 8181:80 --name birdday-card-running --mount type=bind,source="$(pwd)"/src,target=/var/www/html birdday-card
fi

if [ "$1" == "rebuild" -o "$1" == "run" ]; then
    docker start --interactive birdday-card-running
else
    echo "Pass 'rebuild' or 'run'"
fi
