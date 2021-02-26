#!/usr/bin/env bash

set -e

case $ENVIRONMENT in
  staging)
    AUTH=$STAGING_AUTH
    ;;
  production)
    AUTH=$PROD_AUTH
    ;;
  *)
    echo -n "Unknown environment"
    ;;
esac

BUILD_DIR='/temp/build/reap'
CURRENT_DIR='/var/www/reap/current'
RELEASES_DIR='/var/www/reap/releases'
RELEASE=$(date '+%Y%m%d_%H%M%S')
NEW_RELEASE_DIR="$RELEASES_DIR/$RELEASE"
NEW_RELEASE_APP="$NEW_RELEASE_DIR/reap"

echo 'Preparing build...'
mkdir -p ${BUILD_DIR}
ls -all ${BUILD_DIR}
cp -R ${CI_PROJECT_DIR}/config ${CI_PROJECT_DIR}/db ${CI_PROJECT_DIR}/src ${BUILD_DIR}
cp ${CI_PROJECT_DIR}/{bootstrap.php,composer.json,composer.lock,phinx.php,.env} ${BUILD_DIR}
ls -all ${BUILD_DIR}

echo 'Creating new release directory...'
ssh ${AUTH} "mkdir -p $NEW_RELEASE_DIR"

echo 'Deploying service in new release directory...'
scp -r ${BUILD_DIR} ${AUTH}:${NEW_RELEASE_DIR}

echo 'Setting up dependencies...' # Execute in server: sudo chown -R $USER:$USER $HOME/.composer
ssh ${AUTH} "composer config --global --auth gitlab-token.gitlab.com $ACCESS_TOKEN && 
             cd $NEW_RELEASE_APP && composer install --no-dev --no-progress"

echo 'Updating release reference and removing previous...'
ssh ${AUTH} "ln -nfs $NEW_RELEASE_APP $CURRENT_DIR &&
            cd $RELEASES_DIR && ls | grep -v $RELEASE | xargs rm -rf" # remove all except release

echo 'Running DB migrations...'
ssh ${AUTH} "cd $CURRENT_DIR && bin/phinx migrate -e $ENVIRONMENT"

echo 'Cleaning up build...'
rm -rf ${BUILD_DIR}

echo 'Deployment completed.'
