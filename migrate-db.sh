#!/bin/sh

cd /var/www/html
php artisan migrate --force --no-interaction -vvv
if [ $? != 0 ]; then 
  exit 1
else 
  exit 0
fi
