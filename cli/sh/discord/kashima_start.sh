#!/usr/bin/env bash
# sh /home/njr-sys/public_html/cli/sh/discord/kashima_start.sh

logfile="/home/njr-sys/public_html/logs/discord/system/run_pid.log"
date=`date +"%Y%m%d_%I%M%S"`

# 起動
nohup php /home/njr-sys/public_html/application/cli/commons/cli_load.php 'CliDiscordBot' 'test' >> /home/njr-sys/public_html/logs/discord/run/${date}.log &

# pid 保存
pid_new=$!

#起動したカシマのpidを保存
echo ${pid_new} >> ${logfile}

echo start KASHIMA-EXE pid: ${pid_new}

exit
