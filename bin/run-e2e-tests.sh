#!/usr/bin/env bash

if [[ "$TRAVIS" == "true" ]]; then
    npm install --only=dev --prefix ./cypress/
    composer install --no-dev
fi

wp_host='localhost'
windows=`echo $OSTYPE | grep -i -e "win" -e "msys" -e "cygw" | wc -l`
args='-it';
if [[ $windows -gt 0 ]]; then
    wp_host=`docker-machine ip`
    args=''
fi

# exit on error
set -e

export CYPRESS_HOST=$wp_host

# test ONLY free
export CYPRESS_SPEC_TO_RUN="shortcode_free.spec.js"
npm run cypress:run

# test ONLY free lazy loading
export CYPRESS_SPEC_TO_RUN="shortcode_lazy_free.spec.js"
npm run cypress:run

# import feed
export CYPRESS_SPEC_TO_RUN="import_feed_free.spec.js"
npm run cypress:run

