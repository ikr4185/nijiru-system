#!/usr/bin/env bash
# sh /home/njr-sys/public_html/cli/sh/kashima_start.sh

logfile="/home/njr-sys/public_html/cli/logs/KASHIMA_run.log"

# 起動
nohup php /home/njr-sys/public_html/cli/KASHIMA-EXE2.php &

# pid 保存
pid_new=$!

#起動したカシマのpidを保存
echo ${pid_new} >> ${logfile}

echo start KASHIMA-EXE pid: ${pid_new}

exit 0
