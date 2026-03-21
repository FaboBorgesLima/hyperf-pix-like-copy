#!/bin/bash

# creates a key pair for jwt signing and verification in auth service
function create_secrets() {
    if [ -f "./auth/keys/private-key.pem" ] && [ -f "./auth/keys/public-key.pem" ]; then
        echo "Keys already exist, skipping generation."
        return
    fi
    mkdir -p ./auth/keys
    openssl genpkey -algorithm RSA -out ./auth/keys/private-key.pem -pkeyopt rsa_keygen_bits:2048
    openssl rsa -pubout -in ./auth/keys/private-key.pem -out ./auth/keys/public-key.pem
}

function create_env() {
    local service=$1
    if [ ! -f "./$service/.env" ]; then
        cp "./$service/.env.example" "./$service/.env"
    fi
}

function composer_install() {
    local service=$1
    docker run -w /data/project \
    -v "./$service:/data/project" \
    --rm -it \
    --privileged -u root \
    composer install --ignore-platform-reqs
}

create_secrets

create_env "auth"
composer_install "auth"