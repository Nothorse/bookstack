#!/usr/bin/env php
<?php
namespace EBookLib;
use EBookLib;
define('SHELL', true);
require_once(dirname(__DIR__) . "/config.php");
require dirname(__DIR__) . "/vendor/autoload.php";

/**
 * Command line script ot add book
 *
 */
class AddBook extends CommandLine {

  /**
   * initParams -- Defining all parameters
   */
  protected function initParams() {
    $this->setCommand('refreshlibrary');
    $this->addParam(array('-d:','--directory:'), 'DIRECTORY', 'If a different basedirectory is desired');
  }

  /**
   * main -- main logic flow.
   * script checks for defined tables and then exports data and schema as demanded
   *
   * @return void
   */
  public function main() {
    global $argv;
  $files = glob(BASEDIR . '*/*.epub');
  $files += glob(BASEDIR . '*/*/*.epub');
  $files += glob(BASEDIR . '*/*/*/*.epub');
  $intactlog = dirname(__DIR__) . "/intact_books.log";
  $failedlog = dirname(__DIR__) . "/damaged_books.log";
  echo "$intactlog / $failedlog\n";
    foreach($files as $file) {
        $book = Ebook::createFromFile($file);
        if (!$book instanceof Ebook) {
            #echo "FAIL: $file: $book\n";
            file_put_contents($failedlog, "$book\n", FILE_APPEND);
            continue;
        }
        #echo "$file => Book: {$book->title} by {$book->author}\n\n";
        file_put_contents($intactlog, "{$book->title} by {$book->author} (" . basename($file) . ")\n", FILE_APPEND);
    }
    exit;
    $lib = new Library();
    if($file && file_exists($file) && strpos($file, '.epub') > 0) {
      $book = new Ebook($file);
      $book->file = $book->cleanupFile($file);
      //$growl  = "/usr/local/bin/terminal-notifier ";
      //$growl .= " -n 'Giles (Ebooklib)' ";
      $growl = str_ireplace(array("'", '"', ';'), '', $book->title) . " by " . $book->author;
      $result = $lib->insertBook($book);
      $lib->logThis("Added book $growl (" . $book->getFullFilePath() . ')');
      return true;
    } else {
      echo "No ebook given.\n";
      return false;
    }
  }

}
$s = new AddBook();
return $s->main();
