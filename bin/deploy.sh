#!/bin/bash

# We run this just one time, for a first job from the buid and just at once after_deploy hook.
if ! [ $AFTER_DEPLOY_RUN ] && [ "$TRAVIS_PHP_VERSION" == "7.0" ]; then

    # Flag the run in order to not be trigged again on the next after_deploy.
        export AFTER_DEPLOY_RUN=1;
        echo " Started deploy script. ";

    # Setup git username and email.

        git config user.name "selul"
        git config user.email ${GITHUB_EMAIL}
        git fetch

     # Check if we already have a tag with this version.
     if ! git rev-parse "v$THEMEISLE_VERSION" >/dev/null 2>&1
        then

    # Send changelog changes to git.
        git checkout $MASTER_BRANCH
        git add -v .

        # We use [skip ci] in message to prevent this commit to trigger the build.
        git commit -a -m "[AUTO][skip ci] Updating changelog for v"$THEMEISLE_VERSION
        git push --quiet "https://${GITHUB_TOKEN}@github.com/$UPSTREAM_REPO.git" HEAD:$MASTER_BRANCH

    # Tag the new release.
        git tag -a "v$THEMEISLE_VERSION" -m "[AUTO] Release of $THEMEISLE_VERSION ";
        git push --quiet "https://${GITHUB_TOKEN}@github.com/$UPSTREAM_REPO.git"  --tags ;
        sleep 5;

    # Sends the api call for creating the release.
    # We use this as the travis release provider does not offer any way
    # to set the body of the release.
        API_JSON='{"tag_name": "v'$THEMEISLE_VERSION'","target_commitish": "'$MASTER_BRANCH'","name": "v'$THEMEISLE_VERSION'","body": "'$CHANGES'","draft": false,"prerelease": false}';
        curl -s --data  "$API_JSON" "https://api.github.com/repos/$UPSTREAM_REPO/releases?access_token="$GITHUB_TOKEN  > /dev/null;
     fi
     # Send update to the store
        STORE_JSON='{"version": "'$THEMEISLE_VERSION'","id": "'$THEMEISLE_ID'","body": "'$CHANGES'"}';
        curl -s  -H "Content-Type: application/json" -H "x-themeisle-auth: $THEMEISLE_AUTH"  --data "$STORE_JSON" "$STORE_URL/wp-json/ti-endpoint/v1/update_changelog_new/" > /dev/null

     # Send data to demo server.
        grunt sftp

     # Upload to Wordpress SVN
     if [ ! -z "$WPORG_PASS" ]; then

            svn co -q "http://svn.wp-plugins.org/$THEMEISLE_REPO" svn

            # Copy new content to svn trunk.
            rsync -r -p --delete --exclude=".*" dist/* svn/trunk

            # Create new SVN tag.
            mkdir -p svn/tags/$THEMEISLE_VERSION
            rsync -r -p  dist/* svn/tags/$THEMEISLE_VERSION
            # Add new files to SVN
            svn stat svn | grep '^?' | awk '{print $2}' | xargs -I x svn add x@
            # Remove deleted files from SVN
            svn stat svn | grep '^!' | awk '{print $2}' | xargs -I x svn rm --force x@

            svn stat svn

            # Commit to SVN
            svn commit svn   --no-auth-cache  -m "Release  v$THEMEISLE_VERSION" --username $WPORG_USER --password $WPORG_PASS
            # Remove svn dir.
            rm -fR svn

	 fi

fi;
