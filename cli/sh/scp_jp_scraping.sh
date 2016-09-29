#!/usr/bin/env bash

# sh /home/njr-sys/public_html/cli/sh/scp_jp_scraping.sh

#curl http://njr-sys.net/scpReader/scp/001 -I
#sleep 3s
#
#curl http://njr-sys.net/scpReader/scp/001 -I
#sleep 3s

# ログファイルパス
logfile="/home/njr-sys/public_html/cli/logs/scp_jp-scraping.log"

# 実行ログの保存
date=`date`
echo -e "[Start]\t"${date} >> ${logfile}

for i in {2..1999};
	do name=$(printf %03d ${i})
	curl http://njr-sys.net/scpReader/scp/${name} -I >/dev/null 2>&1
	echo -e "[scraping]\t"${name} >> ${logfile}
	sleep 10s
done

# 実行ログの保存
echo -e "[Done]\t"${date} >> ${logfile}

exit
