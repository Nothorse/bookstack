#!/usr/bin/php
<?php
namespace EBookLib;
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
    $this->setCommand('addbook');
    $this->addParam(array('-f:','--file:'), 'FILE', 'Epub File');
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
    $file = $this->getArgument('FILE');
    $lib = new Library();
    if($file && file_exists($file) && strpos($file, '.epub') > 0) {
      $book = new Ebook($file);
      $book->file = $book->cleanupFile($file);
      $growl = str_ireplace(array("'", '"', ';'), '', $book->title) . " by " . $book->author;
      $result = $lib->insertBook($book);
      $lib->logThis("Added book $growl (" . $book->getFullFilePath() . ')');
      $lib->setFree();
      return true;
    } else {
      echo "No ebook given.\n";
      return false;
    }
  }

}
$s = new AddBook();
return $s->main();
