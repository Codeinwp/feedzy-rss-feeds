#!/bin/bash

# We run this on PR or on push to MASTER_BRANCH.
if [ "$TRAVIS_PULL_REQUEST" != "false" ] ||  ( [ "$TRAVIS_EVENT_TYPE" == "push" ] && [ "$TRAVIS_REPO_SLUG" == "$UPSTREAM_REPO" ] && [ "$TRAVIS_BRANCH"  == "$MASTER_BRANCH" ]  && [ "$TRAVIS_PHP_VERSION" == "7.0" ] ) ; then

    . $HOME/.nvm/nvm.sh
    nvm install stable
    nvm use stable

    npm install
    npm install grunt-cli -g

    phpenv local 5.6

    composer selfupdate 1.0.0 --no-interaction
    composer install --no-interaction
    phpenv local --unset

fi;
# We dont install PHPCS if is not a PR.
if [ "$TRAVIS_PULL_REQUEST" != "false" ]; then

          # Install PHPCS.
          pear install pear/PHP_CodeSniffer-2.8.1

          # Install WPCS standards.
          git clone -b master --depth 1 https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git $HOME/wordpress-coding-standards
          phpenv rehash
          phpcs --config-set installed_paths $HOME/wordpress-coding-standards
          phpenv rehash

          # Install wordpress for testing.
          bash bin/install-wp-tests.sh wordpress_test root '' localhost $WP_VERSION
          export PATH="$HOME/.composer/vendor/bin:$PATH"

          # Use phpunit 5.7 as WP dont support 6.
          if [[ ${TRAVIS_PHP_VERSION:0:2} == "7." ]]; then
            composer global require "phpunit/phpunit=5.7.*" ;
          fi;
fi;