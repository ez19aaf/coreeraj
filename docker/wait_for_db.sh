#!/usr/bin/env bash

#
# Script to wait for mysql docker container to startup
#
# REQUIRED PARAMETERS:
#   <CONTAINER> the name of the mysql docker container
#   <USERNAME> the username of the mysql docker container
#   <PASSWORD> the password of the mysql docker container
#

# Check number of arguments parsed
if [[ $# -ne 3 ]]; then
    echo "Usage: bash wait_for_db.sh <CONTAINER> <USERNAME> <PASSWORD>";
    exit 1;
fi

CONTAINER=$1
USERNAME=$2
PASSWORD=$3

echo "Waiting for db connection"

check=$(docker exec ${CONTAINER} mysql -u ${USERNAME} -p${PASSWORD} -e 'status' 2> /dev/null)

while [ -z "$check" ]; do
    echo -n "."
    sleep 5
    check=$(docker exec ${CONTAINER} mysql -u ${USERNAME} -p${PASSWORD} -e 'status' 2> /dev/null)
done
