#!/bin/sh

env | grep -E -v "^(BASTION2_SSH_PUBLIC_KEY=|BASTION1_SSH_PUBLIC_KEY=|HOME=|USER=|MAIL=|LC_ALL=|LS_COLORS=|LANG=|HOSTNAME=|PWD=|TERM=|SHLVL=|LANGUAGE=|_=)" >>/etc/environment

env | grep "$VARS_PREFIX" >>/var/www/html/.env
chown www-data:www-data /var/www/html/.env

if [ -z "$BASTION1_SSH_PUBLIC_KEY" ] || [ "$BASTION1_SSH_PUBLIC_KEY" = "local" ]; then
  echo "Local environment so ssh/cron setup skipped."
else
  if [ -z "$BASTION1_SSH_PUBLIC_KEY" ] || [ -z "$BASTION2_SSH_PUBLIC_KEY" ]; then
    echo "Need your SSH public key as the BASTION1_SSH_PUBLIC_KEY & BASTION2_SSH_PUBLIC_KEY env variable."
    exit 1
  fi

  # Setup cron for refreshing AWS credentials
  service_directory=$(echo "$VARS_PREFIX" | tr '[:upper:]' '[:lower:]')
  service_directory=${service_directory%?}
  cd /usr/local/bin || exit 1
  echo "SHELL=/bin/bash" >cronjobs.txt
  echo "MAILTO=dev@yeppay.io" >>cronjobs.txt
  crontab -l | grep -v tokens:purge >>cronjobs.txt
  echo "0 * * * * . cd /var/www/html && /usr/bin/php7.4 artisan tokens:purge &> /var/www/html/storage/logs/$service_directory/token_purge.log " >>cronjobs.txt
  crontab cronjobs.txt
  service cron start

  # Create a folder to store user's SSH keys if it does not exist.
  USER_SSH_KEYS_FOLDER=~/.ssh
  [ ! -d "$USER_SSH_KEYS_FOLDER" ] && mkdir -p $USER_SSH_KEYS_FOLDER

  # Copy contents from the `BASTION1_SSH_PUBLIC_KEY` & `BASTION2_SSH_PUBLIC_KEY` environment variable
  # to the `${USER_SSH_KEYS_FOLDER}/authorized_keys` file.
  # The environment variable must be set when the container starts.
  echo "$BASTION1_SSH_PUBLIC_KEY" >${USER_SSH_KEYS_FOLDER}/authorized_keys
  echo "$BASTION2_SSH_PUBLIC_KEY" >>${USER_SSH_KEYS_FOLDER}/authorized_keys

  # Clear the `SSH_PUBLIC_KEY` environment variable.
  unset BASTION1_SSH_PUBLIC_KEY
  unset BASTION2_SSH_PUBLIC_KEY

  # Start the SSH daemon.
  /usr/sbin/sshd -D
fi
