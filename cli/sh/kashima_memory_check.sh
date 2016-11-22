#!/usr/bin/env bash

logfile="/home/njr-sys/public_html/cli/logs/KASHIMA_memory_used.log"

date=`date`
memory_used=`ps aux | grep KASHIMA-EXE2.php | grep -v grep | awk '{print $5}'`
memory_used_81=`ps aux | grep KASHIMA-EXE-site8181.php | grep -v grep | awk '{print $5}'`
server_free=`free | grep - | awk '{print "used:"$3" free:"$4}'`
#server_pmap=`pmap -x 25596 | sed -n '$p'`

#起動したカシマのmemoryを保存
echo -e ${date}"\tjp\t"${memory_used} >> ${logfile}
echo -e ${date}"\t81\t"${memory_used_81} >> ${logfile}
echo -e ${date}"\t"${server_free} >> ${logfile}
echo -e "----------" >> ${logfile}
#echo -e ${date}"\t"${server_pmap} >> ${logfile}

exit 0
