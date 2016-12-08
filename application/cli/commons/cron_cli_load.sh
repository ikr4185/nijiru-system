#!/usr/bin/env bash

arg=$1
arg2=$2
arg3=$3

# 環境変数の変更
export NVM_BIN=/home/njr-sys/.nvm/versions/node/v6.9.1/bin
export NVM_IOJS_ORG_MIRROR=https://iojs.org/dist
export NVM_NODEJS_ORG_MIRROR=https://nodejs.org/dist
export NVM_PATH=/home/njr-sys/.nvm/versions/node/v6.9.1/lib/node
export NVM_DIR=/home/njr-sys/.nvm
export PATH=/home/njr-sys/.nvm/versions/node/v6.9.1/bin:/usr/local/bin:/bin:/usr/bin:/usr/local/sbin:/usr/sbin:/sbin:/home/njr-sys/bin:/home/njr-sys/public_html/node_modules/.bin

cd /home/njr-sys/public_html/application/
php composer.phar ncl ${arg} ${arg2} ${arg3}

exit 0
