#!/usr/bin/env bash

# scpjp
isAlive=`ps aux | grep KASHIMA-EXE2.php | grep -v grep | grep -v kashima_check.sh | wc -l`

if [ ${isAlive} != 1 ]; then
	sh /home/njr-sys/public_html/cli/sh/kashima_reboot.sh
fi

# site8181
isAlive=`ps aux | grep KASHIMA-EXE-site8181.php | grep -v grep | grep -v kashima_check.sh | wc -l`

if [ ${isAlive} != 1 ]; then
	sh /home/njr-sys/public_html/cli/sh/kashima_81_reboot.sh
fi

# Discord
isAlive=`ps aux | grep CliDiscordBot | grep -v grep | grep -v kashima_check.sh | wc -l`

if [ ${isAlive} != 1 ]; then
	sh /home/njr-sys/public_html/cli/sh/discord/kashima_reboot.sh
fi

exit 0
