
# install WP
wp  --allow-root core install --url="http://localhost:8888/" --admin_user="wordpress" --admin_password="wordpress" --admin_email="test1@xx.com" --title="test" --skip-email

wp  --allow-root  plugin install classic-editor --activate

# activate
wp  --allow-root plugin activate feedzy-rss-feeds

# set this constant so that the specific hooks are loaded
wp  --allow-root config set TI_CYPRESS_TESTING true --raw

# create terms
wp  --allow-root term create category c_feedzy-1
wp  --allow-root term create post_tag t_feedzy-1

# debugging
wp  --allow-root config set WP_DEBUG true --raw
wp  --allow-root config set WP_DEBUG_LOG true --raw
wp  --allow-root config set WP_DEBUG_DISPLAY false --raw

