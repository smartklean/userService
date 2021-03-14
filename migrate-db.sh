#!/bin/sh

cd /var/www/html || exit 1
php artisan migrate --force --no-interaction -vvv
if [ $? != 0 ]; then 
  echo "DB migration error. Exiting with error."
  exit 1
fi

privkey=./storage/oauth-private.key
pubkey=./storage/oauth-public.key
if [ -f "$privkey" ] && [ -f "$pubkey" ]; then
  echo "Passport key files already exist. No need to run php artisan passport:install."
  exit 0
else
  php artisan passport:install --force
  if [ $? != 0 ]; then 
    echo "Passport install error. Exiting with error."
    exit 1
  fi
fi
