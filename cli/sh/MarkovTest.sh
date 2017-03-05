#!/usr/bin/env bash

STRING=$1
DATE=`date "+%Y%m%d-%H%M%S"`
TMP_FILE="/home/njr-sys/public_html/test/${DATE}.dat"
echo ${STRING} > ${TMP_FILE}
RESULT=`echo ${TMP_FILE} | xargs ~/local/bin/mecab -d ~/local/lib/mecab/dic/ipadic-neologd -b 100000 -Ochasen`
rm -rf ${TMP_FILE}

echo "${RESULT}"

exit 0
