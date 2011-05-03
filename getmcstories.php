#!/usr/sbin/php-cgi -Cq
<?php
date_default_timezone_set('Europe/Vienna');
/**
 * Needs to manually require the parent class, as it is called outside the weblication framework
 */
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/commandline.cls.php");
require_once(__DIR__ . "/ebook.cls.php");
require_once(__DIR__ . "/library.cls.php");
require_once(__DIR__ . "/epubgen/EPub.php");
require_once(__DIR__ . "/epubgen/lib.uuid.php");
require_once(__DIR__ . "/epubgen/Zip.php");
#require_once(__DIR__ . "/epubgen/EpubChapterSplitter.php");
$path = '/usr/lib/php';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

class getMcStories extends CommandLine {
  
  /**
   * initParams -- Defining all parameters
   */
  protected function initParams() {
    $this->setCommand('addbook');
    $this->addParam('-u:,--url:', 'URL', 'URL');
  }
  
  public function main() {
    $url = $this->getArgument('URL');
    if(strlen($url) > 5) {
      require_once(__DIR__."/sites/mcstories.php");
      $d = new Downloader();
      $d->retrieveBook($url);
    }
  }

}


$s = new getMcStories();
$s->main();
