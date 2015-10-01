#!/bin/sh

PORT=${1:-'8080'}
php -S localhost:$PORT -t web web/index.php

# @todo Use next command after AssetsController.php deletion
# php -S localhost:$PORT -t web
