#!/usr/bin/php
<?php
//ini_set('register_argc_argv', 'On');
// ini_set('display_errors', '0');     # don't show any errors...
//error_reporting(E_ALL | E_STRICT);
define('SHELL', true);
require_once(__DIR__ . "/config.php");
require "vendor/autoload.php";
/**
 * Needs to manually require the parent class, as it is called outside the weblication framework
 */
$path = '/usr/lib/php/pear';

set_include_path(get_include_path() . PATH_SEPARATOR . $path);

class AddBook extends CommandLine {

  use EbookLib;
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
    $log = __DIR__ . "/tmp/ebooklib.log";
    $file = $this->getArgument('FILE');
    error_log("ADDBOOK Given $file\n", 3, $log);
    if($file && file_exists($file) && strpos($file, '.epub') > 0) {
      error_log("ADDBOOK Trying to add $file\n",3, $log);
      $book = new Ebook($file);
      $book->file = $book->cleanupFile($file);
      //$growl  = "/usr/local/bin/terminal-notifier ";
      //$growl .= " -n 'Giles (Ebooklib)' ";
      $growl = str_ireplace(array("'", '"', ';'), '', $book->title) . " by " . $book->author;
      error_log("ADDED $growl (" . $book->getFullFilePath() .")\n",3, $log);
      $lib = new Library();
      $lib->insertBook($book);
      return true;
    } else {
      echo "No ebook given.\n";
      return false;
    }
  }

}
$s = new AddBook();
return $s->main();
