#!/usr/bin/env bash
# rootで実行する事
# sh /home/njr-sys/public_html/cli/sh/discord/kashima_reboot.sh

logfile="/home/njr-sys/public_html/logs/discord/system/run_pid.log"

#現在のプロセスIDを取得
pid_now=`sed -n '$p' ${logfile}`

echo kill pid: ${pid_now}

#停止
kill -9 ${pid_now}

sleep 3s

# 起動
nohup php /home/njr-sys/public_html/application/cli/commons/cli_load.php 'CliDiscordBot' 'test' > /dev/null 2>&1 &

# pid 保存
pid_new=$!

#停止したカシマのpidを削除
last=`sed -n '$!p' ${logfile}`
echo ${last} > ${logfile}

#起動したカシマのpidを保存
echo ${pid_new} >> ${logfile}

echo reboot KASHIMA-EXE pid: ${pid_new}

exit
