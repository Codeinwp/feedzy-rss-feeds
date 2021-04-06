#!/usr/bin/env bash

sleep_time=15
echo "Sleeping for $sleep_time..."
sleep $sleep_time

# install WP
docker exec  feedzy_wordpress wp --quiet core install --url="http://$wp_host:8888/" --admin_user="wordpress" --admin_password="wordpress" --admin_email="test1@xx.com" --title="test" --skip-email

# install required external plugins
docker exec feedzy_wordpress chown -R www-data:www-data /var/www/html/
docker exec feedzy_wordpress wp plugin install classic-editor --activate

# so that debug.log is created
docker exec  feedzy_wordpress chmod 777 /var/www/html/wp-content

# so that composer does not fail
docker exec  feedzy_wordpress chmod -R 777 /var/www/html/wp-content/plugins/feedzy-rss-feeds-pro

# activate
docker exec  feedzy_wordpress wp --quiet plugin activate feedzy-rss-feeds

# set this constant so that the specific hooks are loaded
docker exec  feedzy_wordpress wp --quiet config set TI_CYPRESS_TESTING true --raw

# create terms
docker exec  feedzy_wordpress wp --quiet term create category c_feedzy-1
docker exec  feedzy_wordpress wp --quiet term create post_tag t_feedzy-1

# debugging
docker exec  feedzy_wordpress wp --quiet config set WP_DEBUG true --raw
docker exec  feedzy_wordpress wp --quiet config set WP_DEBUG_LOG true --raw
docker exec  feedzy_wordpress wp --quiet config set WP_DEBUG_DISPLAY false --raw



