#!/usr/bin/env bash
# rootで実行する事
# sh /home/njr-sys/public_html/cli/sh/discord/kashima_reboot.sh

#停止
sh /home/njr-sys/public_html/cli/sh/discord/kashima_stop.sh

sleep 3s

# 起動
sh /home/njr-sys/public_html/cli/sh/discord/kashima_start.sh

echo reboot KASHIMA-EXE

exit
