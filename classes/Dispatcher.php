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
   * microtime request
   * @var int
   */
  private $time;

  /**
   * Dispatcher constructor.
   */
  public function __construct() {
    $this->library = new Library();
    $this->display = new BrowserDisplay();
  }


  /**
   * handle all requests
   * @param array $path request path
   * @param int   $time microtime
   */
  public function handleRequest($path, $time) {
    $this->time = $time;
    // workaround for stupid nginx
    if ($path[0] == 'index.php') array_shift($path);
    $handler = 'handle'.$path[0];
    $this->$handler($this->library, $path);
  }

  /**
   * handler
   * @param Library $library   libray
   * @param array   $path path
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
    $this->display->printFooter($this->time);
  }

  /**
   * @param Library $library   library
   * @param array   $path path
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
   * Get all books by an author
   * @param Library $library   library
   * @param array   $path path
   */
  public function handleauthor($library, $path) {
    list($discard,$method, $author) = explode('/', $_SERVER['PATH_INFO']);
    $list = $library->getBookList('added desc', ['author' => [ '=', $path[1]]]);
    $this->display->printHeader();
    if ($author) $this->display->printBookList($list, 'bookswide');
    $this->display->printFooter($this->time);
    exit;
  }

  /**
   * get all books in a series
   * @param Library $library   library
   * @param array   $path path
   */
  public function handleseries($library, $path) {
    list($discard,$method, $author) = explode('/', $_SERVER['PATH_INFO']);
    $list = $library->getBookList('series_volume asc, added desc',
                                  ['series_id' => ['=', $path[1]]]);
    $this->display->printHeader();
    //$alist = $this->listdir_by_author($path, $library);
    if ($author) $this->display->printBookList($list, 'bookswide');
    $this->display->printFooter($this->time);
    exit;
  }

  /**
   * @param Library $library   library
   * @param array   $path path
   */
  public function handletag($library, $path) {
    setcookie('booksel', 'tag', 0, '/');
    setcookie('selval', $path[1], 0, '/');
    $list = $library->getTaggedBooks($path[1]);
    $this->display->printHeader();
    $alist = $library->getTagList(false);
    $this->display->printAuthorList($alist, 'tag', $path[1]);
    if ($path[1]) $this->display->printBookList($list, 'books');
    $this->display->printFooter($this->time);
    exit;
  }

  /**
   * @param Library $library   library
   * @param array   $path path
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
   * @param array   $path path
   */
  public function handleshow($library, $path) {
    $book = $library->getBook($path[1]);
    $this->display->printHeader();
    $book->get_meta();
    echo $this->display->showDetails($book);
    $this->display->printFooter($this->time);
    exit;
  }

  /**
   * Handler for update requests.
   * @param Library $library   library
   * @param array   $path path
   */
  public function handlerefresh($library, $path) {
    $book = $library->getBook($path[1]);
    $library->queueThis($book->getFullFilePath());
    $library->logThis("Update " . $book->title);
    $this->display->printHeader();
    $book->get_meta();
    echo $this->display->showDetails($book);
    $this->display->printFooter($this->time);
    exit;
  }

  /**
   * Handler for download requests.
   * @param Library $library   library
   * @param array   $path path
   */
  public function handleadd($library, $path) {
    $log = $library->getLastLog();
    $this->display->printHeader();
    $tpl = new Template('downloadform');
    echo "<div id='log'>";
    echo $tpl->render([]);
    if (isset($_POST['url'])) {
      $url = $_POST['url'];
      if (strpos($url, 'http') !== false & \strlen($url) > 15) {
        echo "Accepted URL " . $url . " for download.<br />";
        $library->logThis("Download queued for URL $url\n");
        $library->queueThis($url);
      }
    }
    if (isset($_FILES['newbook'])) {
      $fileName = $_FILES['newbook']['name'];
      $fileSize = $_FILES['newbook']['size'];
      $fileTmpName  = $_FILES['newbook']['tmp_name'];
      $fileType = $_FILES['newbook']['type'];
      if ($fileType == 'application/epub+zip') {
        move_uploaded_file($fileTmpName, BASEDIR . "/.incoming/$fileName");
        echo "Moved $fileName to .incoming";
      }
    }
    echo "<hr/>";
    echo "Last log entries:<br>";
    $this->display->printLog($log);
    echo "</div>";
    $this->display->printFooter($this->time);
  }

  /**
   * @param Library $library   library
   * @param array   $path path
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
    $book->series = (isset($_POST['seriesname'])) ? [$_POST['seriesname'], $_POST['series_volume']] : [];
    $book->sortauthor = (isset($_POST['author'])) ? strtolower($_POST['author']) : $book->sortauthor;
    $coverillu = false;
    if (isset($_FILES['illu'])) {
      $fileName = $_FILES['illu']['name'];
      $fileSize = $_FILES['illu']['size'];
      $fileTmpName  = $_FILES['illu']['tmp_name'];
      $fileType = $_FILES['illu']['type'];
      if (!isset($_FILES['illu']['error'])) {
        $coverillu = true;
        move_uploaded_file($fileTmpName, dirname(__DIR__) . "/tmp/illu.jpg");
      }
    }
    if ($coverillu || (isset($_POST['updatecover']) && $_POST['updatecover'])) {
      $book->updateCover($_POST['updatecover']);
    }
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
    $library->logThis("Metadata updated for " . $book->title . " Result: $res");
    }
    echo (isset($_POST['editactive'])) ? $this->display->showDetails($book) :
      $this->display->getEditForm($book, $url);
    $this->display->printFooter($this->time);
    exit;
  }

  /**
   * @param Library $library   library
   * @param array   $path path
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
   * @param array   $path path
   */
  public function handledelete($library, $path) {
    $book = $library->getBook($path[1]);
    $library->deleteBook($book);
    header("Location: http://".SERVER.BASEURL);
    exit;
  }

  /**
   * @param Library $library   library
   * @param array   $path path
   */
  public function handlelog($library, $path) {
    $log = $library->getLastLog(50, Library::DEBUG);
    $this->display->printHeader();
    echo "<p>Last log entries:</p>";
    $this->display->printLog($log);
    echo "</div>";
    $this->display->printFooter($this->time);
    exit;
  }

  /**
   * @param Library $library   library
   * @param array   $path path
   */
  public function handlefree($library, $path) {
    $library->setFree();
    exit;
  }

  /**
   * @param Library $library   library
   * @param array   $path path
   */
  public function handlesearch($library, $path) {
    if (isset($path[1])) {
      $search = "%" . $path[1] . "%";
      $where = [
        'title' => ['LIKE', $search],
        'author' => ['LIKE', $search],
        'summary' => ['LIKE', $search],
        'tag' => ['LIKE', $search],
      ];
      $list = $library->getBooklist('added desc', $where);
      $this->display->printBookList($list, 'bookswide');
    } else {
      $list = $library->getBooklist('added desc', '', true);
      $this->display->printBookList($list, 'bookswide');

    }
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
   * @param  array   $path
   * @param  Library $library
   * @param  bool    $limit
   * @return mixed
   */
  public function listdir_by_date($path, $library, $limit = false){
    return $library->getBookarray('added desc', null, $limit);
  }

  /**
   * @param array   $path request path
   * @param Library $library   Library
   * @return array
   */
  public function listdir_by_author($path, $library){
    return $library->getBooklist('sortauthor asc');
  }

  /**
   * @param array   $path
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
