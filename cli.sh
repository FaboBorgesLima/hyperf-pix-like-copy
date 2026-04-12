#!/bin/bash

function console() {
  # using hyperf-skeleton because i'm without network and i have this image on cache
  docker run --name console \
  -w /data/project \
  -v .:/data/project \
  -p 9501:9501 --rm -it \
  --privileged -u root \
  --entrypoint /bin/sh \
  hyperf-skeleton
}

function test-all() {
  local only_errors=false
  local services=("auth" "gateway" "transaction" "notification")
  local failed=()

  for arg in "$@"; do
    [[ "$arg" == "--only-errors" ]] && only_errors=true
  done

  for service in "${services[@]}"; do
    local output
    output=$(docker compose exec "$service" composer test 2>&1)
    local exit_code=$?

    if [ $exit_code -eq 0 ]; then
      $only_errors || echo "==> $service: PASSED"
    else
      echo "==> $service: FAILED"
      echo "$output"
      failed+=("$service")
    fi
  done

  if [ ${#failed[@]} -gt 0 ]; then
    echo ""
    echo "FAILED services: ${failed[*]}"
    exit 1
  else
    echo ""
    echo "All tests passed."
  fi
}

case "${1:-console}" in
  test-all) shift; test-all "$@" ;;
  console)  console ;;
  *)        echo "Unknown command: $1"; echo "Usage: $0 [console|test-all [--only-errors]]"; exit 1 ;;
esac