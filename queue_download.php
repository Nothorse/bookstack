#!/usr/bin/php
<?php
/**
 * Pass a queued download to fanficfare.
 * Use as cronjob.
 * remember to copy your personal.ini into the ebooklib dir
 */
require __DIR__ . "/vendor/autoload.php";
use EBookLib\Library as Library;
$LOGFILE = __DIR__ . '/tmp/fff_error.log';
$db = new Library();
$toBequeued = '"' . $db->getQueue() . '"';
$basecmd = "nohup " . FFF . ' -c ' . __DIR__ . '/lib/ebooklib.ini ';
$baseupdateflags = "--update-epub --update-cover ";
$basecmdend = ' >> ' . $LOGFILE . ' 2>&1 &';
$cmdtype = (strpos($toBequeued, 'http') === 0) ? '' : $baseupdateflags;
if ($toBequeued) {
  // first check if a fanficfare process is running
  $cmd = $basecmd . $cmdtype . $toBequeued . $basecmdend;
  exec($cmd);
  $db->setQueueEntryDone($toBequeued);
  $db->logThis("passed $toBequeued to fanficfare");
}
