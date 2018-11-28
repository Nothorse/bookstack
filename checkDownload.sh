#!/bin/bash
DL=`which fanficfare`;
echo $DL;
if [ -f /tmp/download_queued ]; then
URL=`cat /tmp/download_queued`;
$DL $URL;
echo "Passed $URL to fanficfare" >> /tmp/ebooklib.log
fi;