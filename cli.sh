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

console