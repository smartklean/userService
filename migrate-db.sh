#!/bin/sh

cd /var/www/html
php artisan migrate --force --no-interaction -vvv
kill 1
