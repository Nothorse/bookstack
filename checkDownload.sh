#!/bin/bash
DL=`which fanficfare`
if [ -f /tmp/download_queued ]; then
    URL=`cat /tmp/download_queued`
    rm /tmp/download_queued
    nohup $DL "$URL" >> /tmp/ebooklib.log 2>&1 &
    echo "Passed $URL to fanficfare" >> /tmp/ebooklib.log
fi;