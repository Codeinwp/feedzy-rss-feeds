#!/usr/bin/env bash


# Wait for mysql container to be ready.
while docker-compose -f $DOCKER_FILE run --rm -u root cli wp --allow-root db check ; [ $? -ne 0 ];  do
	  echo "Waiting for db to be ready... "
    sleep 1
done
# install WP
docker-compose -f $DOCKER_FILE run  --rm -u root cli bash -c "/var/www/html/bin/cli-setup.sh"



