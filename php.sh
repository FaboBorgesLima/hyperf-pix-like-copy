#!/bin/bash

SERVICE="${1:?Usage: $0 <service> <command...>}"
shift

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Prepend 'php' if the first argument is a .php file
if [[ "${1}" == *.php ]]; then
    set -- php "$@"
fi
function run_in_container() {
    docker run --rm -it \
    -w /opt/www \
    -v "${SCRIPT_DIR}/${SERVICE}:/opt/www" \
    -v "${SCRIPT_DIR}/packages:/opt/packages" \
    --privileged -u root \
    --entrypoint "" \
    hyperf-skeleton \
    "$@"
}

function run_in_docker_compose() {
    docker compose -f "${SCRIPT_DIR}/docker-compose.yaml" exec -it "${SERVICE}" "$@"
}

# Check if the service is running in Docker Compose
if docker compose -f "${SCRIPT_DIR}/docker-compose.yaml" ps "${SERVICE}" | grep -q "Up"; then
    echo "Running command in Docker Compose container..."
    run_in_docker_compose "$@"
else
    echo "Running command in a new Docker container... start docker compose with 'docker compose -f docker-compose.yaml up' to avoid this overhead"
    run_in_container "$@"
fi

