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
    system("/usr/bin/logger ADDBOOK Given $file");
    if($file && file_exists($file) && strpos($file, '.epub') > 0) {
      system("/usr/bin/logger ADDBOOK Trying to add $file");
      $book = new ebook($file);
      $book->file = $book->cleanupFile($file);
      $growl  = "/usr/local/bin/terminal-notifier ";
//       $growl .= " -n 'Giles (Ebooklib)' ";
      $growl .= "-message '" . str_ireplace(array("'", '"', ';'), '', $book->title) . " by " . $book->author . "'";
      $growl .= " -title 'Book added' -open 'http://localhost:8080/index.php/show/" . $book->id ."' -timeout 10";
      system($growl, $out);
      system("/usr/bin/logger ADDBOOK $growl");
      echo $growl;
      $lib = new library();
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
