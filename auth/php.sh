#!/bin/bash

function php() {
    docker compose -f ../docker-compose.yaml exec -it auth "$@"
} 

php "$@"