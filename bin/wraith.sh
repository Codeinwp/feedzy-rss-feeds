#!/usr/bin/env bash
#!/bin/bash

if [ "$TRAVIS_PULL_REQUEST" != "false" ]; then

    # Sending wraith request.

    WRAITH_SLUG=$(node -pe "require('./package.json').wraithSlug")
    WRAITH_FAIL=${WRAITH_FAIL:-5}
    body="{
      'request': {
          'travis_event_type': '$TRAVIS_EVENT_TYPE',
          'travis_pull_request': '$TRAVIS_PULL_REQUEST',
          'travis_pull_request_branch': '$TRAVIS_PULL_REQUEST_BRANCH',
          'travis_pull_request_slug': '$TRAVIS_PULL_REQUEST_SLUG',
          'travis_repo_slug': '$TRAVIS_PULL_REQUEST_SLUG',
          'travis_branch': '$TRAVIS_PULL_REQUEST_BRANCH',
          'wraithSlug': $WRAITH_SLUG,
          'wraithFail': $WRAITH_FAIL
      }
    }"

    echo "Triggering build of $TRAVIS_REPO_SLUG branch $TRAVIS_BRANCH on Travis. Calling Wraith API ..."

    output=$(curl -sw "%{http_code}" -X POST \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -H "Travis-API-Version: 3" \
        -d "${body//\'/\"}" \
        'http://wraith.themeisle.com?api&travis')

    http_code="${output:${#output}-3}"
    if [ ${#output} -eq 3 ]; then
      body=""
    else
      body="${output:0:${#output}-3}"
    fi

    if [ "$http_code" != "200" ]; then
      echo "$output";
      exit 1
    else
     echo "$output";
     exit 0
    fi
fi
