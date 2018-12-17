#!/bin/bash
# Absolute path to this script, e.g. /home/user/bin/foo.sh
SCRIPT=$(readlink -f "$0")
# Absolute path this script is in, thus /home/user/bin
SCRIPTPATH=$(dirname "$SCRIPT")
QFILE=${SCRIPTPATH}/tmp/download_queued
LOGFILE=${SCRIPTPATH}/tmp/ebooklib.log
#echo $QFILE $LOGFILE
DL=`which fanficfare`
if [ -f $QFILE ]; then
    URL=`cat $QFILE`
    nohup $DL "$URL" >> $LOGFILE 2>&1 &
    echo "Passed $URL to fanficfare" >> $LOGFILE
    rm -f $QFILE
fi;