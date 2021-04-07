#!/usr/bin/env bash
docker-compose -f $DOCKER_FILE run  --rm -u root cli bash -c "wp --quiet plugin deactivate classic-editor"