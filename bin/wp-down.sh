#!/usr/bin/env bash
docker-compose -f $DOCKER_FILE down
docker volume prune --force