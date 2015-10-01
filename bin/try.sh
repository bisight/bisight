#!/bin/sh

# DATABASE='linkorb_sandbox_bisight'
DATABASE='bisight' # This name hardcoded :(
CONFIG='config.json'

set -e

finish() {

    # Restore parameters
    if [ -f $CONFIG.bak ]; then
        cp $CONFIG.bak $CONFIG
        rm $CONFIG.bak

        echo "$GREEN"
        echo "Your $CONFIG restored"
        echo "$NC"
    fi
}

trap finish EXIT

RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color

if [ ! -f composer.json ]; then
    echo "$RED"
    echo "You need to run this command from project root."
    echo "Right from directory where composer.json placed."
    echo "$NC"
    exit;
fi

echo "Test mysql connection..."
mysql -u root -e "SELECT 'SUCCESS' as Status;" > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "$RED"
    echo "Mysql connection FAILED"
    echo "You need to have mysql server running with empty root password."
    echo "$NC"
    exit;
fi

# Backup parameters
if [ -f $CONFIG ]; then
    cp $CONFIG $CONFIG.bak
fi

cp $CONFIG.dist $CONFIG

if [ ! -f /share/config/database/$DATABASE.conf ]; then
    sudo mkdir -p /share/config/database/
    sudo cp app/config/$DATABASE.conf.dist /share/config/database/$DATABASE.conf
fi

composer install
# bower install
# npm install
# grunt

mysql -u root -e "DROP DATABASE IF EXISTS $DATABASE;"
mysqladmin -u root create $DATABASE

vendor/bin/dbtk-schema-loader schema:load app/schema.xml $DATABASE --apply
vendor/bin/haigha fixtures:load test/fixture/example-data.yml $DATABASE

./bin/run.sh
