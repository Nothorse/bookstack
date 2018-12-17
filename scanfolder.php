<?php
/**
 * scanFolder
 */
define('SHELL', true);
require_once(__DIR__ . "/config.php");
require "vendor/autoload.php";
$path = '/usr/lib/php/pear';


class scanFolder extends CommandLine {

  use EbookLib;

  protected function initParams() {
    $this->addUsage('scan the bookdir');
  }

  public function main() {
    $library = new Library();
    $base = BASEDIR . '/';
    $authordirs = scandir($base);
    foreach ($authordirs as $authordir) {
      if (strpos($authordir, '.') === 0) continue;
//      echo $authordir ."\n";
      $bookdirs = scandir($base .$authordir);
      foreach ($bookdirs as $bookdir) {
        if (strpos($bookdir, '.') === 0) continue;
//        echo "$authordir/$bookdir/\n";
        $single = scandir($base . $authordir . '/' . $bookdir);
        foreach ($single as $file) {
          if (strpos($file, '.epub') === false) continue;
          $path = $authordir . '/' . $bookdir . '/' . $file;
          $id = $library->getBookIdByPath($path);
          //echo "$id: $path\n";
          if (!$id) {
            $book = new Ebook($path);
            //$book->file = $book->cleanupFile($file);
            echo "To be added: " . $book->title . ' by ' . $book->author . "\n";
            $library->insertBook($book);
          }
        }
      }
    }
  }

}

$sf = new ScanFolder();
$sf->main();
