#!/bin/sh

cd /var/www/html
php artisan migrate --force --no-interaction -vvv
if [ $? != 0 ]; then 
  kill -6 $(ps -ef | grep /usr/bin/supervisord | grep -v grep | awk '{print $2}')
else 
  kill $(ps -ef | grep /usr/bin/supervisord | grep -v grep | awk '{print $2}')
fi
