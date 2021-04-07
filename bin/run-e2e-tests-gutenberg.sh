#!/usr/bin/env bash
docker-compose -f $DOCKER_FILE run  --rm -u root cli bash -c "wp --allow-root plugin deactivate classic-editor"