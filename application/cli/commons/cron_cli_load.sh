#!/usr/bin/env bash

arg=$1
arg2=$2
arg3=$3

cd /home/njr-sys/public_html/application/
php composer.phar ncl ${arg} ${arg2} ${arg3}

exit 0
