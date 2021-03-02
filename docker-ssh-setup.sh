#!/bin/sh

env | grep -E -v "^(BASTION2_SSH_PUBLIC_KEY=|BASTION1_SSH_PUBLIC_KEY=|HOME=|USER=|MAIL=|LC_ALL=|LS_COLORS=|LANG=|HOSTNAME=|PWD=|TERM=|SHLVL=|LANGUAGE=|_=)" >> /etc/environment

env | grep USERSWS_ >> /var/www/html/.env
chown www-data:www-data /var/www/html/.env

if [ -z "$BASTION1_SSH_PUBLIC_KEY"  ] || [ -z "$BASTION2_SSH_PUBLIC_KEY"  ]; then
  echo "Need your SSH public key as the BASTION1_SSH_PUBLIC_KEY & BASTION2_SSH_PUBLIC_KEY env variable."
  exit 1
fi

# Create a folder to store user's SSH keys if it does not exist.
USER_SSH_KEYS_FOLDER=~/.ssh
[ ! -d "$USER_SSH_KEYS_FOLDER" ] && mkdir -p $USER_SSH_KEYS_FOLDER

# Copy contents from the `BASTION1_SSH_PUBLIC_KEY` & `BASTION2_SSH_PUBLIC_KEY` environment variable
# to the `${USER_SSH_KEYS_FOLDER}/authorized_keys` file.
# The environment variable must be set when the container starts.
echo "$BASTION1_SSH_PUBLIC_KEY" > ${USER_SSH_KEYS_FOLDER}/authorized_keys
echo "$BASTION2_SSH_PUBLIC_KEY" >> ${USER_SSH_KEYS_FOLDER}/authorized_keys

# Clear the `SSH_PUBLIC_KEY` environment variable.
unset BASTION1_SSH_PUBLIC_KEY
unset BASTION2_SSH_PUBLIC_KEY

# Start the SSH daemon.
/usr/sbin/sshd -D
