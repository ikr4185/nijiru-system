#!/usr/bin/env bash
# rootで実行する事
# sh /home/njr-sys/public_html/cli/sh/discord/kashima_reboot.sh

# リブートログ
logfile="/home/njr-sys/public_html/logs/discord/system/reboot.log"
echo `date +"%Y%m%d_%I%M%S"` >> ${logfile}

#停止
sh /home/njr-sys/public_html/cli/sh/discord/kashima_stop.sh >> ${logfile}

sleep 3s

# 起動
sh /home/njr-sys/public_html/cli/sh/discord/kashima_start.sh >> ${logfile}

echo reboot KASHIMA-EXE
echo ---------------------------------------- >> ${logfile}


exit
