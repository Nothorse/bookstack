<?php
namespace EBookLib;

require_once(dirname(__DIR__) . "/config.php");
/**
 * Base class for sqlite interaction with schema and queries.
 */
class Library{

  /**
   * database sqlite file
   * @var \SQLite3;
   */
  private $db;

  /**
   * constructor with optional db name.
   * @param string $db name of db file
   */
  public function __construct($db = null) {
    global $debug;
    if (!$db) {
      $db = BASEDIR . '/.library.db';
    }
    $time = microtime(true);
    $this->db = $this->getdb($db);
    $this->checkTables();
    $debug['library startup'] = microtime(true) - $time;
  }

  /**
   * open sqlite database
   * @param  string $dbname db file name
   * @return \SQLite3       sqlite object
   */
  private function getdb($dbname = "library.sqlite") {
    $base= new \SQLite3($dbname);
    $base->busyTimeout(5000);
    $base->exec('PRAGMA journal_mode = wal;');

    if (!$base)
    {
      echo "SQLite NOT supported.\n";
      exit($err);
    }
    else
    {
      return $base;
    }
  }

  /**
   * utility method to create or modify all sqlite tables.
   * @return bool result
   */
  private function checkTables() {
    return true;
    $q=$this->db->query("PRAGMA table_info(books)");
    if ($q->fetchArray() < 1) {
        if (!$this->db->exec("
            CREATE TABLE books (
                id INTEGER NOT NULL PRIMARY KEY,
                title VARCHAR ( 255 ) NOT NULL,
                author VARCHAR(255) NOT NULL,
                sortauthor VARCHAR(255) NOT NULL,
                file VARCHAR(255) NOT NULL,
                summary TEXT,
                md5id varchar(255) NOT NULL UNIQUE,
                added timestamp NOT NULL
                )")
        ) exit ("Create SQLite Database Error\n");
    }
    $q=$this->db->query("PRAGMA table_info(tags)");
    if ($q->fetchArray() < 1) {
        if (!$this->db->exec("
            CREATE TABLE tags (
                id INTEGER NOT NULL PRIMARY KEY,
                tag VARCHAR ( 255 ) NOT NULL UNIQUE
                )")
        ) exit ("Create SQLite Database Error\n");
    }
    $q=$this->db->query("PRAGMA table_info(taggedbooks)");
    if ($q->fetchArray() < 1) {
        if (!$this->db->exec("
            CREATE TABLE taggedbooks (
                bookid INTEGER NOT NULL,
                tagid  INTEGER NOT NULL
                )")
        ) exit ("Create SQLite Database Error\n");
    }
    $q=$this->db->query("PRAGMA table_info(activitylog)");
    if ($q->fetchArray() < 1) {
        if (!$this->db->exec("
            CREATE TABLE activitylog (
                logid INTEGER NOT NULL PRIMARY KEY,
                datestamp  TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                entry TEXT NOT NULL
                )")
        ) exit ("Create SQLite Database Error\n");
    }
    $q=$this->db->query("PRAGMA table_info(downloadqueue)");
    if ($q->fetchArray() < 1) {
        if (!$this->db->exec("
            CREATE TABLE downloadqueue (
                queueid INTEGER NOT NULL PRIMARY KEY,
                datestamp  TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                entry VARCHAR(255) NOT NULL,
                done INTEGER DEFAULT 0 NOT NULL
                )")
        ) exit ("Create SQLite Database Error\n");
    }
  }

  /**
   * insert metadata of book into db
   * @param  Ebook $ebook ebook
   * @return bool|int     success or error
   */
  public function insertBook($ebook) {
    $qry = "insert or replace into books (title,
                               author,
                               sortauthor,
                               file,
                               summary,
                               md5id,
                               added)
                               values
                   ('".\SQLite3::escapeString($ebook->title)."',
                    '".\SQLite3::escapeString($ebook->author)."',
                    '".\SQLite3::escapeString($ebook->sortauthor)."',
                    '".\SQLite3::escapeString($ebook->file)."',
                    '".\SQLite3::escapeString($ebook->summary)."',
                    '".\SQLite3::escapeString($ebook->id)."',
                    '".time()."')";
    $success = $this->db->exec($qry);
    $qry = "select * from books where md5id = '".$ebook->id."'";
    $res = $this->db->query($qry);
    $row = $res->fetcharray();
    $bookid = $row['id'];
    $success = $success && $this->db->exec("DELETE FROM taggedbooks WHERE bookid = '$bookid'");
    if (empty($ebook->tags)) $ebook->tags[] = 'untagged';
    foreach($ebook->tags as $id => $tag) {
      $tag = \SQLite3::escapeString($tag);
      $qry = "select id from tags where tag = '$tag'";
      $tagid = $this->db->querySingle($qry);
      if (!$tagid) {
        $this->db->exec("insert into tags (tag) values ('$tag')");
        $tagid = $this->db->querySingle($qry);
      }
      $success = $success && $this->db->exec("INSERT INTO taggedbooks (bookid, tagid) values ('$bookid', '$tagid')");
    }
    return ($success) ? true : $this->db->lastErrorCode();
  }

  /**
   * @param Ebook $ebook
   */
  public function updateBook($ebook) {
    $qry = "update books
              SET  title = '".\SQLite3::escapeString($ebook->title)."',
                  author = '".\SQLite3::escapeString($ebook->author)."',
              sortauthor = '".\SQLite3::escapeString($ebook->sortauthor)."',
                 summary = '".\SQLite3::escapeString($ebook->summary)."'
             WHERE md5id = '".\SQLite3::escapeString($ebook->id)."'";
    $this->db->exec($qry);
    $qry = "select * from books where md5id = '".$ebook->id."'";
    $res = $this->db->query($qry);
    $row = $res->fetcharray();
    $bookid = $row['id'];
    $this->db->exec("DELETE FROM taggedbooks WHERE bookid = '$bookid'");
    foreach($ebook->tags as $id => $tag) {
      $qry = "select id from tags where tag = '$tag'";
      $tagid = $this->db->querySingle($qry);
      if (!$tagid) {
        $this->db->exec("insert into tags (tag) values ('$tag')");
        $tagid = $this->db->querySingle($qry);
      }
      $this->db->exec("INSERT INTO taggedbooks (bookid, tagid) values ('$bookid', '$tagid')");
    }
  }

  /**
   * get basic metadata for specific book identified by md5id
   * @param  string $md5id md5id of book
   * @return Ebook         Ebook with basic metadata
   */
  public function getBook($md5id) {
    $qry = "select * from books where md5id = '".$md5id."'";
    $res = $this->db->query($qry);
    $row = $res->fetcharray();
    $book = new Ebook();
    $book->title = $row['title'];
    $book->author = $row['author'];
    $book->sortauthor = $row['sortauthor'];
    $book->file = $row['file'];
    $book->summary = $row['summary'];
    $book->id = $row['md5id'];
    $tagquery = "select tag from tags join taggedbooks on taggedbooks.tagid = tags.id where taggedbooks.bookid = '" . $row['id'] . "'";
    $tagres = $this->db->query($tagquery);
    while($tagrow = $tagres->fetchArray()) {
      $book->tags[] = $tagrow['tag'];
    }
    return $book;
  }

  /**
   * get list of books
   * @param  string $order order by
   * @param  string $where where term
   * @param  bool   $limit limit
   * @return array         books
   */
  public function getBooklist($order = 'added desc', $where = '', $limit = false) {
    $res = $this->bookQuery($order, $where, $limit);
    $booklist = array();
    while ($row = $res->fetchArray()) {
        $book = new Ebook();
        $book->title = $row['title'];
        $book->author = $row['author'];
        $book->sortauthor = $row['sortauthor'];
        $book->file = $row['file'];
        $book->summary = $row['summary'];
        $book->id = $row['md5id'];
        $book->updated = $row['added'];
        $book->tags = explode (',', $row['tags']);
        $booklist[$book->id] = $book;
    }
    return $booklist;
  }

  /**
   * get a book array from a query
   * @param  string $order order by
   * @param  string $where where term
   * @param  bool   $limit use a limit
   * @return array        book array
   */
  public function getBookarray($order = 'added desc', $where = '', $limit = false) {
    global $debug;
    $res = $this->bookQuery($order, $where, $limit);
    $booklist = array();
    $time = microtime(true);
    while ($row = $res->fetchArray()) {
      $book = new MetaBook();
      $book->title = $row['title'];
      $book->author = $row['author'];
      $book->sortauthor = $row['sortauthor'];
      $book->file = $row['file'];
      $book->summary = $row['summary'];
      $book->id = $row['md5id'];
      $book->updated = $row['added'];
      $book->tags = explode (',', $row['tags']);
      $booklist[$book->id] = $book;
    }
    $endtime = microtime(true) - $time;
    $debug["Fetcharray"] = $endtime;
    return $booklist;

  }

  /**
   * get list of authors
   * @param  string $order order by
   * @return array         name, books
   */
  public function getAuthorlist($order = 'sortauthor asc') {
    $booklist = array();
    $qry = "select author, title, sortauthor from books order by $order";
    $res = $this->db->query($qry);
    while ($row = $res->fetchArray()) {
      if(strlen($row['title']) > 0) {
        $booklist[$row['author']]['name'] = $row['author'];
        $booklist[$row['author']]['books'][] = $row['title'];
      }
    }
    return $booklist;
  }

  /**
   * get list of tags
   * @param  bool $updatedtags including update date tags
   * @return array                array of books
   */
  public function getTagList($updatedtags = true) {
    $time = microtime(true);
    $booklist = array();
    $where = (!$updatedtags) ? " where tag not like 'last update%'":'';
    $qry = "select tag, count(bookid) as bookcount from tags" .
      " left join taggedbooks on tagid = id" .
      " $where group by tag order by tag asc";
    $res = $this->db->query($qry);
    global $debug;
    $debug['getTags'] = microtime(true) - $time;
    while ($row = $res->fetchArray()) {
      if(strlen($row['tag']) > 0) {
        $booklist[$row['tag']]['name'] = $row['tag'];
        $booklist[$row['tag']]['books'][] = $row['bookcount'];
      }
    }
    $debug['with subqueries'] = microtime(true) - $time;
    return $booklist;
  }

  /**
   * query to get books tagged with $tag
   * @param  string $tag   tag name
   * @param  string $order order by
   * @return array         array of books
   */
  public function getTaggedBooks($tag, $order = 'added desc') {
    $booklist = array();
    $qry = "select * from books join taggedbooks on taggedbooks.bookid = books.id join tags on tags.id = taggedbooks.tagid where tag = '$tag' order by $order";
    $res = $this->db->query($qry);
    while ($row = $res->fetchArray()) {
        $book = new Ebook();
        $book->title = $row['title'];
        $book->author = $row['author'];
        $book->sortauthor = $row['sortauthor'];
        $book->file = $row['file'];
        $book->summary = $row['summary'];
        $book->id = $row['md5id'];
        $book->updated = $row['added'];
        $booklist[$book->sortauthor.$book->title] = $book;
    }
    return $booklist;
  }

  /**
   * @param Ebook $book
   */
  public function deleteBook($book) {
    $bookid = $this->db->querySingle('select id from books where md5id =\''.$book->id."'");
    $this->db->exec("delete from books where id = '$bookid'");
    $this->db->exec("delete from taggedbooks where bookid = '$bookid'");
    rename(dirname($book->getFullFilePath()), TRASH .basename(dirname($book->file)));
  }

  /**
   * fix tags, if untagged
   * @return void no return
   */
  public function fixTags() {
    $untagged = "select id from tags where tag = 'untagged'";
    $tagexists = $this->db->querySingle($untagged);
    if (empty($tagexists)) {
      $this->db->exec("insert into tags ('tag') values ('untagged')");
      $tagexists = $this->db->querySingle($untagged);
    }
    $untaggedquery = "select id, title from books where id not in (select bookid from taggedbooks)";
    $untaggedlist = $this->db->query($untaggedquery);
    while ($row = $untaggedlist->fetchArray()) {
      echo $row['id'] . ': ' . $row['title'] . "<br>\n";
      $bookid = $row['id'];
      $sql = "insert into taggedbooks (bookid, tagid) values ($bookid, $tagexists)";
      $this->db->exec($sql);
    }
    $this->db->exec("update books set summary = 'No summary' where summary = ''");
  }

  /**
   * get the md5id form the path
   * @param  string $path path to epub
   * @return string       md5id
   */
  public function getBookIdByPath($path) {
    $path = \SQLite3::escapeString($path);
    $qry = "select id from books where file = '$path'";
    $bookid = $this->db->querySingle($qry);
    if (empty($bookid)) {
      return false;
    } else {
      return $bookid;
    }
  }

  /**
   * Book query.
   * @param string  $order order by
   * @param string  $where where clause
   * @param bool $limit limit
   * @return \SQLite3Result
   */
  protected function bookQuery($order, $where, $limit)
  {
    $this->logThis("where: " .$where);
    $time = microtime(true);
    $limstr = ($limit) ? " LIMIT 30" : '';
    $qry = "select title, author, sortauthor, file, summary, md5id, added, " .
      "group_concat(tag) " .
      "as tags from books" .
      " join taggedbooks on books.id = bookid " .
      " join tags on tagid = tags.id " .
      " $where " .
      " group by books.id" .
      " order by $order $limstr";
    $res = $this->db->query($qry);
    $this->logThis($qry);
    global $debug;
    $debug["DB Select"] = microtime(true) - $time;
    return $res;
  }

  /**
   * logger into database.
   * @param  string $msg log this
   * @return bool        worked
   */
  public function logThis($msg) {
     $query = "INSERT INTO activitylog (entry) VALUES ('" .
       \SQLite3::escapeString($msg) . "')";
     $this->db->exec($query);
  }

  /**
   * add a queued download into database.
   * @param  string $url log this
   * @return bool        worked
   */
  public function queueThis($url) {
     $query = "INSERT INTO downloadqueue (entry) VALUES ('" .
       \SQLite3::escapeString($url) . "')";
     return $this->db->exec($query);
  }

  /**
   * get last 20 log entries by default.
   * @param  int    $limit limit
   * @return array         array datestamp=>logentry
   */
  public function getLastLog($limit = 20) {
    $qry = "select datestamp, entry from activitylog order by datestamp desc";
    $qry .= ($limit) ? " limit 30" : '';
    $loglines = $this->db->query($qry);
    $result = [];
    while ($row = $loglines->fetchArray()) {
      $result[] = $row;
    }
    return $result;
  }

  /**
   * get current queue.
   * @return string url/filepath
   */
  public function getQueue() {
    $query = "SELECT entry FROM downloadqueue WHERE done = 0 ";
    $query .= "ORDER BY datestamp desc LIMIT 1";
    $res = $this->db->querySingle($query);
    return ($res) ? $res : false;
  }

  /**
   * set an entry to done after passing it to fanficfare
   * @param string $entry url or path
   * @return bool success
   */
  public function setQueueEntryDone($entry) {
    $query = "UPDATE downloadqueue SET done = 1 where entry = '$entry'";
    return $this->db->exec($query);
  }

}
