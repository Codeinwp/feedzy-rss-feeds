#!/usr/bin/env bash
docker exec $args feedzy_wordpress wp --quiet plugin deactivate classic-editor
