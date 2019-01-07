#!/usr/bin/php
<?php
/**
 * Pass a queued download to fanficfare.
 * Use as cronjob.
 * remember to copy your personal.ini into the ebooklib dir
 */
require dirname(__DIR__) . "/vendor/autoload.php";
require dirname(__DIR__) . '/config.php';
use EBookLib\Library as Library;
$LOGFILE = dirname(__DIR__) . '/tmp/fff_error.log';
$db = new Library();
$entry = $db->getQueue();
$toBequeued = '"' . $entry . '"';
$basecmd = "nohup " . FFF . ' -c ' . __DIR__ . '/lib/ebooklib.ini ';
$baseupdateflags = "--update-epub --update-cover ";
$basecmdend = ' >> ' . $LOGFILE . ' 2>&1 &';
$cmdtype = (strpos($toBequeued, 'http') === 0) ? '' : $baseupdateflags;
if ($entry) {
  // todo first check if a fanficfare process is running
  $cmd = $basecmd . $cmdtype . $toBequeued . $basecmdend;
  exec($cmd);
  $db->setQueueEntryDone($entry);
  $db->logThis("passed $toBequeued to fanficfare");
}
// check .incoming dir in BASEDIR
$incoming = BASEDIR . '/.incoming';
if (file_exists($incoming) && is_dir($incoming)) {
  $dirlist = scandir($incoming);
  foreach ($dirlist as $item) {
    if (strpos($item, '.epub') !== false) {
      $cmd = __DIR__ . '/addbook.php -f ' . escapeshellarg($incoming.'/'.$item);
      exec($cmd, $output, $return);
      if ($return != 0) {
        $db->logThis("Addbook error: " . implode(', ', $output));
      }
    }
  }
}
