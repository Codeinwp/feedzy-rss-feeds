#!/usr/bin/env bash

# if on windows, find out the IP using `docker-machine ip` and provide the IP as the host.
wp_host='localhost'
#wp_host='192.168.99.100'

# sleep for sometime till WP initializes successfully
sleep_time=15
echo "Sleeping for $sleep_time..."
sleep $sleep_time

# install WP
docker exec -it feedzy_wordpress wp --quiet core install --url="http://$wp_host:8888/" --admin_user="wordpress" --admin_password="wordpress" --admin_email="test1@xx.com" --title="test" --skip-email

# install required external plugins
docker exec -it feedzy_wordpress chown -R www-data:www-data /var/www/html/
docker exec -it feedzy_wordpress wp plugin install classic-editor --activate

# so that debug.log is created
docker exec -it feedzy_wordpress chmod 777 /var/www/html/wp-content

# so that composer does not fail
docker exec -it feedzy_wordpress chmod -R 777 /var/www/html/wp-content/plugins/feedzy-rss-feeds

# activate
docker exec -it feedzy_wordpress wp --quiet plugin activate feedzy-rss-feeds

# set this constant so that the specific hooks are loaded
docker exec -it feedzy_wordpress wp --quiet config set TI_CYPRESS_TESTING true --raw

# create terms
docker exec -it feedzy_wordpress wp --quiet term create category c_feedzy-1
docker exec -it feedzy_wordpress wp --quiet term create post_tag t_feedzy-1

# debugging
docker exec -it feedzy_wordpress wp --quiet config set WP_DEBUG true --raw
docker exec -it feedzy_wordpress wp --quiet config set WP_DEBUG_LOG true --raw
docker exec -it feedzy_wordpress wp --quiet config set WP_DEBUG_DISPLAY false --raw

# download an image for the media upload box
docker exec -it feedzy_wordpress wp media import "https://s.w.org/style/images/wp-header-logo.png"



