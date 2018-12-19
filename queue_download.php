#!/usr/bin/php
<?php
/**
 * Pass a queued download to fanficfare.
 * Use as cronjob.
 */
require __DIR__ . "/vendor/autoload.php";
use EBookLib\Library as Library;
$LOGFILE = __DIR__ . '/tmp/fff_error.log';
$db = new Library();
$toBequeued = $db->getQueue();
if ($toBequeued) {
  $cmd = "nohup " . FFF . ' "' . $toBequeued . '" >> ' . $LOGFILE . ' 2>&1 &';
  exec($cmd);
  $db->setQueueEntryDone($toBequeued);
  $db->logThis("queued $toBequeued for download");
}
