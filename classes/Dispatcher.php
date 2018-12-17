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
  private $db;

  /**
   * @var opdsdisplay
   */
  private $display;

  /**
   * Dispatcher constructor.
   */
  public function __construct() {
    $this->db = new Library();
    $this->display = new BrowserDisplay();
  }


  /**
   * @param $path
   */
  public function handleRequest($path) {
    $handler = 'handle'.$path[0];
    $this->$handler($this->db, $path);
  }

  /**
   * handler
   * @param Library $db   libray
   * @param string  $path path
   */
  public function handle($db, $path) {
    setcookie('booksel', '');
    setcookie('selval', '');
    header("X-Clacks-Overhead: GNU Terry Pratchett");
    $this->display->printHeader();
    $sort = (isset($_GET['sort'])) ? $_GET['sort'] : 'index';
    switch ($sort) {
      case 'name':
        $list = $this->listdir_by_name($path, $db);
        $this->display->printBookList($list, 'bookswide');
        break;
      case 'author':
        $list = $this->listdir_by_author($path, $db);
        $this->display->printBookList($list, 'bookswide');
        break;
      case 'date':
        $list = $this->listdir_by_date($path, $db);
        $this->display->printBookList($list, 'bookswide');
        break;
      case 'tags':
        $list = $db->getTagList(false);
        $this->display->printAuthorList($list, 'tag');
        break;
      case 'recent':
        $list = $this->listdir_by_date($path, $db);
        $this->display->printBookList($list, 'bookswide');
        break;
      default:
        $list = $this->listdir_by_date($path, $db, 20);
        $this->display->printBookList($list, 'bookswide');
    }
    $this->display->printFooter();
  }

  /**
   * @param Library $db   library
   * @param string  $path path
   */
  public function handleget($db, $path) {
    $book = $db->getBook($path[1]);
    #print_r($book);
    header("Content-Type: application/epub");
    echo file_get_contents($book->getFullFilePath());
    exit;
  }

  /**
   *
   */
  public function handlefixtags() {
      $this->db->fixTags();
      $this->display->debug('fix tags');
  }

  /**
   * @param Library $db   library
   * @param string  $path path
   */
  public function handleauthor($db, $path) {
    setcookie('booksel', 'author', 0, '/');
    list($discard,$method, $author) = explode('/', $_SERVER['PATH_INFO']);
    setcookie('selval', $author, 0, '/');
    $list = $db->getBookList('added desc', 'where author = \'' . SQLite3::escapeString($path[1]) . '\'');
    $this->display->printHeader();
    $alist = $this->listdir_by_author($path, $db);
    $this->display->printAuthorList($alist, 'author', $author);
    if ($author) $this->display->printBookList($list, 'books');
    $this->display->printFoot();
    exit;
  }

  /**
   * @param Library $db   library
   * @param string  $path path
   */
  public function handletag($db, $path) {
    setcookie('booksel', 'tag', 0, '/');
    setcookie('selval', $path[1], 0, '/');
    $list = $db->getTaggedBooks($path[1]);
    $this->display->printHeader();
    $alist = $db->getTagList(false);
    $this->display->printAuthorList($alist, 'tag', $path[1]);
    if ($path[1]) $this->display->printBookList($list, 'books');
    $this->display->printFoot();
    exit;
  }

  /**
   * @param Library $db   library
   * @param string  $path path
   */
  public function handlemeta($db, $path) {
    $book = $db->getBook($path[1]);
    $book->get_meta();
    header("Content-Type: text/plain");
    echo $newbook->allmeta->saveXML();
    exit;
  }

  /**
   * @param Library $db   library
   * @param string  $path path
   */
  public function handleshow($db, $path) {
    $book = $db->getBook($path[1]);
    if($this->getproto() == 'epub') {
      header("Content-Type: application/epub");
      echo file_get_contents($book->getFullFilePath());
      exit;
    }
    $type = (isset($_COOKIE['booksel']))? $_COOKIE['booksel'] : 'author';
    $current = (isset($_COOKIE['selval']))? $_COOKIE['selval'] : $book->author;
    setcookie('booksel', '');
    setcookie('selval', '');
    $list = ($type == 'tag') ? $db->getTagList() : $db->getAuthorlist();
    $this->display->printHeader();
    $book->get_meta();
    echo $this->display->showDetails($book);
    $this->display->printFoot();
    exit;
  }

    /**
     * @param Library $db   library
     * @param string  $path path
     */
    public function handleedit($db, $path) {
    $book = $db->getBook($path[1]);
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
      $db->updateBook($book);
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
   * @param Library $db   library
   * @param string  $path path
   */
  public function handleread($db, $path) {
    $book = $db->getBook($path[1]);
    $book->get_meta();
    header("Content-type: text/html");
    echo $book->getChapter($path[2]);
    exit;
  }

  /**
   * @param Library $db   library
   * @param string  $path path
   */
  public function handledelete($db, $path) {
    $book = $db->getBook($path[1]);
    $db->deleteBook($book);
    header("Location: http://".SERVER.BASEURL);
    exit;
  }

  /**
   * @param $file
   * @return mixed
   */
  public function getSuffix($file) {
    list($name, $suffix) = explode('.', $file);
    return $suffix;
  }

  /**
   * List books by date
   * @param  string  $path
   * @param  Library $db
   * @param  bool    $limit
   * @return mixed
   */
  public function listdir_by_date($path, $db, $limit = false){
    return $db->getBookarray('added desc', null, $limit);
  }

  /**
   * @param $path
   * @param $db
   * @return mixed
   */
  public function listdir_by_author($path, $db){
    return $db->getBooklist('sortauthor asc');
  }

  /**
   * @param string  $path
   * @param Library $db
   * @return mixed
   */
  public function listdir_by_name($path, $db){
    return $db->getBooklist('title asc');
  }

  /**
   * @return string
   */
  public function getproto() {
      if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false) {
      return "epub";
    } else {
      return "http";
    }
  }


}
