#!/usr/bin/env bash

cacheDir="/home/njr-sys/public_html/application/views/_compiles"

rm -R ${cacheDir}*
mkdir ${cacheDir}

chown njr-sys:njr-sys ${cacheDir}
chmod 777 ${cacheDir}

exit
