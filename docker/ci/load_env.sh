#!/usr/bin/env bash

set -e

case $ENVIRONMENT in
  staging)
    FE_CLIENT=https://home.staging.survey54.services
    SWITCH_AIRTIME=OFF
    SWITCH_SMS=OFF
    DB_USER=$STAGING_DB_USER
    DB_PASS=$STAGING_DB_PASS
    AFRICASTALKING_USERNAME=sandbox
    AFRICASTALKING_APIKEY=$STAGING_AFRICASTALKING_APIKEY
    TOKEN_KEY=$STAGING_TOKEN_KEY
    MAIL_USER=testing@survey54.com
    MAIL_PASS=$STAGING_MAIL_PASS
    CLOUDINARY_KEY=$STAGING_CLOUDINARY_KEY
    CLOUDINARY_SECRET=$STAGING_CLOUDINARY_SECRET
    CINT_X_API_KEY=$STAGING_CINT_X_API_KEY
    TEXT_LOCAL_API_KEY=$STAGING_TEXT_LOCAL_API_KEY
    ENGAGE_SPARK_TOKEN=$STAGING_ENGAGE_SPARK_TOKEN
    ;;
  production)
    FE_CLIENT=https://survey54.com
    SWITCH_AIRTIME=ON
    SWITCH_SMS=ON
    DB_USER=$PROD_DB_USER
    DB_PASS=$PROD_DB_PASS
    AFRICASTALKING_USERNAME=survey54sms
    AFRICASTALKING_APIKEY=$PROD_AFRICASTALKING_APIKEY
    TOKEN_KEY=$PROD_TOKEN_KEY
    MAIL_USER=no-reply@survey54.com
    MAIL_PASS=$PROD_MAIL_PASS
    CLOUDINARY_KEY=$PROD_CLOUDINARY_KEY
    CLOUDINARY_SECRET=$PROD_CLOUDINARY_SECRET
    CINT_X_API_KEY=$PROD_CINT_X_API_KEY
    TEXT_LOCAL_API_KEY=$PROD_TEXT_LOCAL_API_KEY
    ENGAGE_SPARK_TOKEN=$PROD_ENGAGE_SPARK_TOKEN
    ;;
  *)
    echo -n "Unknown environment"
    ;;
esac

declare -a arr=(

    "SERVICE_NAME=reap"
    "SERVICE_VERSION=1.0"
    "ENVIRONMENT=$ENVIRONMENT"
    "FE_CLIENT=$FE_CLIENT"

    "SWITCH_AIRTIME=$SWITCH_AIRTIME"
    "SWITCH_SMS=$SWITCH_SMS"

    "DB_HOST=localhost"
    "DB_PORT=3306"
    "DB_USER=$DB_USER"
    "DB_PASS=$DB_PASS"
    "DB_NAME=survey54_core"
    "DB_DRIVER=mysql"
    "DB_CHARSET=utf8"
    "DB_COLLATION=utf8_unicode_ci"

    "AFRICASTALKING_USERNAME=$AFRICASTALKING_USERNAME"
    "AFRICASTALKING_APIKEY=$AFRICASTALKING_APIKEY"

    "TOKEN_KEY=$TOKEN_KEY"
    "TENANT=Survey54"

    "MAIL_NAME='Survey54 - The Survey Platform'"
    "MAIL_HOST=smtp.gmail.com"
    "MAIL_PORT=587"
    "MAIL_USER=$MAIL_USER"
    "MAIL_PASS=$MAIL_PASS"
    "MAIL_ENCRYPTION=tls"

    "CLOUDINARY_NAME=survey54"
    "CLOUDINARY_KEY=$CLOUDINARY_KEY"
    "CLOUDINARY_SECRET=$CLOUDINARY_SECRET"

    "CINT_ENDPOINT=https://api.cintworks.net"
    "CINT_X_API_KEY=$CINT_X_API_KEY"

    "TEXT_LOCAL_API_KEY=$TEXT_LOCAL_API_KEY"

    "ENGAGE_SPARK_TOKEN=$ENGAGE_SPARK_TOKEN"
)

printf '%s\n' "${arr[@]}" > '.env'
