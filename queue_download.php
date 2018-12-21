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
$toBequeued = $db->getQueue();
if ($toBequeued) {
  // first check if a fanficfare process is running
  $cmd = "nohup " . FFF . ' -c ' . __DIR__ . '/lib/ebooklib.ini "' .
          $toBequeued . '" >> ' . $LOGFILE . ' 2>&1 &';
  exec($cmd);
  $db->setQueueEntryDone($toBequeued);
  $db->logThis("queued $toBequeued for download");
}
