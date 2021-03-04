#!/bin/bash

trap "exit 1" 6
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
