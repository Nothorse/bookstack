<?php
namespace EBookLib;
require_once('config.php');

/**
 * Class Dispatcher
 */
class Dispatcher {

  /**
   * @var Library
   */
  private $library;

  /**
   * @var BrowserDisplay
   */
  private $display;

  /**
   * Dispatcher constructor.
   */
  public function __construct() {
    $this->library = new Library();
    $this->display = new BrowserDisplay();
  }


  /**
   * handle all requests
   * @param string $path request path
   */
  public function handleRequest($path) {
    $handler = 'handle'.$path[0];
    $this->$handler($this->library, $path);
  }

  /**
   * handler
   * @param Library $library   libray
   * @param string  $path path
   */
  public function handle($library, $path) {
    setcookie('booksel', '');
    setcookie('selval', '');
    header("X-Clacks-Overhead: GNU Terry Pratchett");
    $this->display->printHeader();
    $sort = (isset($_GET['sort'])) ? $_GET['sort'] : 'index';
    switch ($sort) {
      case 'name':
        $list = $this->listdir_by_name($path, $library);
        $this->display->printBookList($list, 'bookswide');
        break;
      case 'author':
        $list = $this->listdir_by_author($path, $library);
        $this->display->printBookList($list, 'bookswide');
        break;
      case 'date':
        $list = $this->listdir_by_date($path, $library);
        $this->display->printBookList($list, 'bookswide');
        break;
      case 'tags':
        $list = $library->getTagList(false);
        $this->display->printAuthorList($list, 'tag');
        break;
      case 'recent':
        $list = $this->listdir_by_date($path, $library);
        $this->display->printBookList($list, 'bookswide');
        break;
      default:
        $list = $this->listdir_by_date($path, $library, 20);
        $this->display->printBookList($list, 'bookswide');
    }
    $this->display->printFooter();
  }

  /**
   * @param Library $library   library
   * @param string  $path path
   */
  public function handleget($library, $path) {
    $book = $library->getBook($path[1]);
    #print_r($book);
    header("Content-Type: application/epub");
    echo file_get_contents($book->getFullFilePath());
    exit;
  }

  /**
   *
   */
  public function handlefixtags() {
      $this->library->fixTags();
      $this->display->debug('fix tags');
  }

  /**
   * @param Library $library   library
   * @param string  $path path
   */
  public function handleauthor($library, $path) {
    setcookie('booksel', 'author', 0, '/');
    list($discard,$method, $author) = explode('/', $_SERVER['PATH_INFO']);
    setcookie('selval', $author, 0, '/');
    $list = $library->getBookList('added desc', 'where author = \'' . \SQLite3::escapeString($path[1]) . '\'');
    $this->display->printHeader();
    $alist = $this->listdir_by_author($path, $library);
    $this->display->printAuthorList($alist, 'author', $author);
    if ($author) $this->display->printBookList($list, 'books');
    $this->display->printFoot();
    exit;
  }

  /**
   * @param Library $library   library
   * @param string  $path path
   */
  public function handletag($library, $path) {
    setcookie('booksel', 'tag', 0, '/');
    setcookie('selval', $path[1], 0, '/');
    $list = $library->getTaggedBooks($path[1]);
    $this->display->printHeader();
    $alist = $library->getTagList(false);
    $this->display->printAuthorList($alist, 'tag', $path[1]);
    if ($path[1]) $this->display->printBookList($list, 'books');
    $this->display->printFoot();
    exit;
  }

  /**
   * @param Library $library   library
   * @param string  $path path
   */
  public function handlemeta($library, $path) {
    $book = $library->getBook($path[1]);
    $book->get_meta();
    header("Content-Type: text/plain");
    echo $newbook->allmeta->saveXML();
    exit;
  }

  /**
   * @param Library $library   library
   * @param string  $path path
   */
  public function handleshow($library, $path) {
    $book = $library->getBook($path[1]);
    if($this->getproto() == 'epub') {
      header("Content-Type: application/epub");
      echo file_get_contents($book->getFullFilePath());
      exit;
    }
    $type = (isset($_COOKIE['booksel']))? $_COOKIE['booksel'] : 'author';
    $current = (isset($_COOKIE['selval']))? $_COOKIE['selval'] : $book->author;
    setcookie('booksel', '');
    setcookie('selval', '');
    $list = ($type == 'tag') ? $library->getTagList() : $library->getAuthorlist();
    $this->display->printHeader();
    $book->get_meta();
    echo $this->display->showDetails($book);
    $this->display->printFoot();
    exit;
  }

  /**
   * Handler for update requests.
   * @param Library $library   library
   * @param string  $path path
   */
  public function handlerefresh($library, $path) {
    $book = $library->getBook($path[1]);
    $library->queueThis($book->getFullFilePath());
    $library->logThis("Update " . $book->title);
    $type = (isset($_COOKIE['booksel']))? $_COOKIE['booksel'] : 'author';
    $current = (isset($_COOKIE['selval']))? $_COOKIE['selval'] : $book->author;
    setcookie('booksel', '');
    setcookie('selval', '');
    $list = ($type == 'tag') ? $library->getTagList() : $library->getAuthorlist();
    $this->display->printHeader();
    $book->get_meta();
    echo $this->display->showDetails($book);
    $this->display->printFoot();
    exit;
  }

  /**
   * Handler for download requests.
   * @param Library $library   library
   * @param string  $path path
   */
  public function handleadd($library, $path) {
    $log = $library->getLastLog();
    $this->display->printHeader();
    $tpl = new Template('downloadform');
    echo "<div id='log'>";
    echo $tpl->render([]);
    if (isset($_POST['url'])) {
      $url = $_POST['url'];
      echo "Accepted URL " . $url . " for download.<hr />";
      $library->logThis("Download queued for URL $url\n");
      $library->queueThis($url);
    }
    echo "Last log entries:<br>";
    $this->display->printLog($log);
    echo "</div>";
    $this->display->printFooter();
  }

  /**
   * @param Library $library   library
   * @param string  $path path
   */
  public function handleedit($library, $path) {
  $book = $library->getBook($path[1]);
  $book->get_meta();
  $book->id = $path[1];
  $url = $_SERVER['PHP_SELF'];
  $this->display->printHeader();
  if (isset($_POST['editactive'])) {
    $book->title = (isset($_POST['title'])) ? $_POST['title']:$book->title;
    $book->author = (isset($_POST['author'])) ? $_POST['author']:$book->author;
    $book->sortauthor = (isset($_POST['author'])) ? strtolower($_POST['author']):$book->sortauthor;
    if (isset($_POST['tags'])) {
      $tags = explode(',', $_POST['tags']);
      $book->tags = array();
      foreach($tags as $tag) {
        $book->tags[] = trim($tag);
      }
    }
    $book->summary = (isset($_POST['summary'])) ? $_POST['summary']:$book->summary;
    $library->updateBook($book);
    $res = $book->modify_meta();
    //setcookie('editresult', $res);
    } else {
      //setcookie('editresult', '');
    }
    $type = $_COOKIE['booksel'];
    $current = $_COOKIE['selval'];
    //setcookie('booksel', '');
    //setcookie('selval', '');
    echo (isset($_POST['editactive'])) ? $this->display->showDetails($book) :
      $this->display->getEditForm($book, $url);
    $this->display->printFoot();
    exit;
  }

  /**
   * @param Library $library   library
   * @param string  $path path
   */
  public function handleread($library, $path) {
    $book = $library->getBook($path[1]);
    $book->get_meta();
    header("Content-type: text/html");
    echo $book->getChapter($path[2]);
    exit;
  }

  /**
   * @param Library $library   library
   * @param string  $path path
   */
  public function handledelete($library, $path) {
    $book = $library->getBook($path[1]);
    $library->deleteBook($book);
    header("Location: http://".SERVER.BASEURL);
    exit;
  }

  /**
   * @param string $file filename
   * @return string
   */
  public function getSuffix($file) {
    list($name, $suffix) = explode('.', $file);
    return $suffix;
  }

  /**
   * List books by date
   * @param  string  $path
   * @param  Library $library
   * @param  bool    $limit
   * @return mixed
   */
  public function listdir_by_date($path, $library, $limit = false){
    return $library->getBookarray('added desc', null, $limit);
  }

  /**
   * @param string  $path request path
   * @param Library $library   Library
   * @return array
   */
  public function listdir_by_author($path, $library){
    return $library->getBooklist('sortauthor asc');
  }

  /**
   * @param string  $path
   * @param Library $library
   * @return mixed
   */
  public function listdir_by_name($path, $library){
    return $library->getBooklist('title asc');
  }

  /**
   * @deprecated old stuff remains of odps support
   * @return string
   */
  public function getproto() {
    return "http";
  }


}
