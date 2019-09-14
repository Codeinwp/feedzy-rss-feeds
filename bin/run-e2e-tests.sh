#!/usr/bin/env bash
composer install --no-dev
npm install

# exit on error
set -e

# test ONLY free
export CYPRESS_SPEC_TO_RUN="shortcode_free.js"
npm run cypress:run


# docker exec -it feedzy_wordpress cat /var/www/html/wp-content/debug.log
