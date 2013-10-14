#!/usr/bin/php
<?php
//ini_set('register_argc_argv', 'On');
// ini_set('display_errors', '0');     # don't show any errors...
//error_reporting(E_ALL | E_STRICT);
define('SHELL', true);
/**
 * Needs to manually require the parent class, as it is called outside the weblication framework
 */
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/commandline.cls.php");
require_once(__DIR__ . "/ebook.cls.php");
require_once(__DIR__ . "/library.cls.php");
$path = '/usr/lib/php/pear';

set_include_path(get_include_path() . PATH_SEPARATOR . $path);

class AddBook extends CommandLine {

  /**
   * initParams -- Defining all parameters
   */
  protected function initParams() {
    $this->setCommand('addbook');
    $this->addParam('-f:,--file:', 'FILE', 'Epub File');
    $this->addParam('-d:,--directory:', 'DIRECTORY', 'If a different basedirectory is desired');
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
    if($file) {
      $book = new ebook($file);
      $book->file = $book->cleanupFile($file);
      $growl  = "/usr/local/bin/growlnotify ";
//       $growl .= " -n 'Giles (Ebooklib)' ";
      $growl .= "-m '" . $book->title . " by " . $book->author . "'";
      $growl .= " -t 'Book added'";
      system($growl, $out);
      system('/usr/bin/logger $growl');
      echo $growl;
      $lib = new library();
      $lib->insertBook($book);
    } else {
      echo "No ebook given.\n";
    }
  }

}
$s = new AddBook();
$s->main();
