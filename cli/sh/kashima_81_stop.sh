#!/usr/bin/env bash
# sh /home/njr-sys/public_html/cli/sh/kashima_81_stop.sh

logfile="/home/njr-sys/public_html/cli/logs/KASHIMA_81_run.log"

#現在のプロセスIDを取得
pid_now=`sed -n '$p' ${logfile}`

echo kill pid: ${pid_now}

#停止
kill -9 ${pid_now}

#停止したカシマのpidを削除
last=`sed -n '$!p' ${logfile}`
echo ${last} > ${logfile}

exit 0
