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

function copy_secrets() {
    if [ -f "./gateway/keys/public-key.pem" ]; then
        echo "Public key already exists in gateway, skipping copy."
    else
        mkdir -p ./gateway/keys
        cp ./auth/keys/public-key.pem ./gateway/keys/
    fi
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
    -v "$(pwd)/$service:/data/project" \
    -v "$(pwd)/packages:/data/packages" \
    --rm -it \
    --privileged -u root \
    composer install --ignore-platform-reqs
}

function migrate_service() {
    local service=$1
    ./php.sh "$service" php bin/hyperf.php migrate
}

create_secrets
copy_secrets

create_env "auth"
composer_install "auth"

create_env "gateway"
composer_install "gateway"

create_env "transaction"
composer_install "transaction"

docker compose -f docker-compose.yaml up -d

migrate_service "auth"
migrate_service "gateway"
migrate_service "transaction"

TIMES=12 # wait up to 1 minute for containers to be healthy
# see if containers are healthy before exiting
while ! docker compose -f docker-compose.yaml ps | grep "healthy" > /dev/null; do
    echo "Waiting for containers to be healthy..."
    sleep 5
    TIMES=$((TIMES - 1))
    if [ $TIMES -le 0 ]; then
        echo "Containers did not become healthy in time, exiting."
        exit 1
    fi
done

docker compose -f docker-compose.yaml stop

echo "Setup complete 😁"
echo "Run 'docker compose -f docker-compose.yaml up' to start the services."