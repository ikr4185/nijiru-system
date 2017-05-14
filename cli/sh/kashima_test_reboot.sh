#!/usr/bin/env bash

# sh /home/njr-sys/public_html/cli/sh/kashima_test_reboot.sh

rebootLogFile="/home/njr-sys/public_html/cli/logs/kashima_test_reboot.log"
echo `date +"%Y%m%d_%I%M%S"` >> ${rebootLogFile}

logfile="/home/njr-sys/public_html/cli/logs/KASHIMA_test_run.log"

#現在のプロセスIDを取得
pid_now=`sed -n '$p' ${logfile}`

echo kill pid: ${pid_now}
echo kill pid: ${pid_now} >> ${rebootLogFile}

#停止
kill -9 ${pid_now}

sleep 3s

# 起動
nohup php /home/njr-sys/public_html/cli/KASHIMA-test.php &

# pid 保存
pid_new=$!

#停止したカシマのpidを削除
last=`sed -n '$!p' ${logfile}`
echo ${last} > ${logfile}

#起動したカシマのpidを保存
echo ${pid_new} >> ${logfile}

echo reboot KASHIMA-EXE pid: ${pid_new}
echo reboot KASHIMA-EXE pid: ${pid_new} >> ${rebootLogFile}
echo ---------------------------------------- >> ${rebootLogFile}

exit 0
