<?php
namespace EBookLib;

use SQLite3;

require_once(dirname(__DIR__) . "/config.php");
/**
 * Base class for sqlite interaction with schema and queries.
 */
class Library{

  /**
   * standard log level
   * @var int
   */
  const NORMAL = 1;

  /**
   * debug log level
   * @var int
   */
  const DEBUG  = 2;
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
   * insert metadata of book into db
   * @param  Ebook $ebook ebook
   * @return bool|int     success or error
   */
  public function insertBook($ebook) {
    // $qry = "insert or replace into books (title,
    //                            author,
    //                            sortauthor,
    //                            file,
    //                            summary,
    //                            md5id,
    //                            added)
    //                            values
    //                ('".\SQLite3::escapeString($ebook->title)."',
    //                 '".\SQLite3::escapeString($ebook->author)."',
    //                 '".\SQLite3::escapeString($ebook->sortauthor)."',
    //                 '".\SQLite3::escapeString($ebook->file)."',
    //                 '".\SQLite3::escapeString($ebook->summary)."',
    //                 '".\SQLite3::escapeString($ebook->id)."',
    //                 '".time()."')";
    // $success = $this->db->exec($qry);
    $fields = [
      'title'      => ['string', \SQLite3::escapeString($ebook->title)],
      'author'     => ['string', \SQLite3::escapeString($ebook->author)],
      'sortauthor' => ['string', \SQLite3::escapeString($ebook->sortauthor)],
      'file'       => ['string', \SQLite3::escapeString($ebook->file)],
      'summary'    => ['string', \SQLite3::escapeString($ebook->summary)],
      'md5id'      => ['string', \SQLite3::escapeString($ebook->id)],
      'added'      => ['num', time()]
    ];
    $success = $this->insert($fields, 'books', true);

    $qry = "select * from books where md5id = '".$ebook->id."'";
    $res = $this->db->query($qry);
    $row = $res->fetcharray();
    $bookid = $row['id'];
    // Tags
    $success = $success && $this->db->exec("DELETE FROM taggedbooks WHERE bookid = '$bookid'");
    if (empty($ebook->tags)) $ebook->tags[] = 'untagged';
    foreach($ebook->tags as $id => $tag) {
      $tag = \SQLite3::escapeString($tag);
      $qry = "select id from tags where tag = '$tag'";
      $tagid = $this->db->querySingle($qry);
      if (!$tagid) {
        $this-insert(['tag' => ['string', $tag]], 'tags');
        $tagid = $this->db->querySingle($qry);
      }
      $ids = ['bookid' => ['num', $bookid],
              'tagid'  => ['num', $tagid]];
      $success = $success && $this->insert($ids, 'taggedbooks');
    }
    $success = $this->updateSeries($ebook, $success);
    return ($success) ? true : $this->db->lastErrorCode();
  }

  /**
   * @param Ebook $ebook
   */
  public function updateBook($ebook) {
    $fields = [
      'title' => ['string', $ebook->title],
      'author' => ['string', $ebook->author],
      'sortauthor' => ['string', $ebook->sortauthor],
      'summary' => ['string', $ebook->summary],
    ];
    $success = $this->update($fields, 'books', ['md5id' => ['=', $ebook->id]]);

    $qry = "select * from books where md5id = '".$ebook->id."'";
    $res = $this->db->query($qry);
    $row = $res->fetcharray();
    $bookid = $row['id'];
    $this->db->exec("DELETE FROM taggedbooks WHERE bookid = '$bookid'");
    foreach($ebook->tags as $id => $tag) {
      $qry = "select id from tags where tag = '$tag'";
      $tagid = $this->db->querySingle($qry);
      if (!$tagid) {
        $this-insert(['tag' => ['string', $tag]], 'tags');
        $tagid = $this->db->querySingle($qry);
      }
      $ids = ['bookid' => ['num', $bookid],
              'tagid'  => ['num', $tagid]];
      $success = $success && $this->insert($ids, 'taggedbooks');
    }
    $success = $this->updateSeries($ebook, true);
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
    $book->seriesId = $row['series_id'];
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
  public function getBooklist($order = 'added desc', $where = '', $limit = 20) {
    $res = $this->bookQuery($order, $where, $limit);
    $booklist = array();
    foreach ($res as $row) {
      error_log(print_r($row, true));
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
  public function getBookarray($order = 'added desc', $where = [], $limit = false) {
    global $debug;
    //$res = $this->bookQuery($order, $where, $limit);
    $limit = ($limit) ? 30 : false;
    $result = $this->select(['title', 'author', 'sortauthor', 'file', 'summary',
                              'md5id', 'added', 'group_concat(tag) as tags'],
                            [
                              ['books', ''],
                              ['taggedbooks', 'books.id = bookid'],
                              ['tags', 'tagid = tags.id']
                            ],
                            [], 'books.id', $order, $limit);
    $booklist = array();
    $time = microtime(true);
    foreach ($result as $row) {
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
   * @return array         id, name
   */
  public function getSerieslist() {
    $serieslist = array();
    $qry = "select id, name from series order by name";
    $res = $this->db->query($qry);
    while ($row = $res->fetchArray()) {
      $serieslist[$row['id']] = $row['name'];
    }
    return $serieslist;
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
    $result = $this->select(['*'],
                            [
                              ['books',''],
                              ['taggedbooks', 'taggedbooks.bookid = books.id'],
                              ['tags', 'tags.id = taggedbooks.tagid']
                            ],
                            ['tag' => ['=', $tag]], false,
                            $order);
    foreach ($result as $key => $row) {
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
    $this->logThis("where: " .print_r($where,true), 2);
    $time = microtime(true);
    $limit = ($limit) ? 30 : false;
    $result = $this->select(['title', 'author', 'sortauthor', 'file', 'summary',
                              'md5id', 'added', 'group_concat(tag) as tags'],
                            [
                              ['books', ''],
                              ['taggedbooks', 'books.id = bookid'],
                              ['tags', 'tagid = tags.id']
                            ],
                            $where, 'books.id', $order, $limit);
    global $debug;
    $debug["DB Select"] = microtime(true) - $time;
    return $result;
  }

  /**
   * logger into database.
   * @param  string $msg   log this
   * @param  int    $level loglevel
   * @return bool        worked
   */
  public function logThis($msg, $level = 1) {
     $query = "INSERT INTO activitylog (entry, level) VALUES ('" .
       \SQLite3::escapeString($msg) . "', $level)";
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
  public function getLastLog($limit = 20, $level = 1) {
    $qry = "select datestamp, entry from activitylog where level <= $level " .
           "order by datestamp desc";
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
    $this->update(['done' => ['bool' => true]], 'downloadqueue',
                  "entry = '$entry'");
  }

  /**
   * Update series information
   * @param Ebook $ebook   ebook
   * @param bool  $success return val
   * @return bool
   */
  private function updateSeries($ebook, $success)
  {
    if ($ebook->getSeriesName() != '') {
      $this->logThis('has series', 2);
      $sqry = "select * from series";
      $sres = $this->db->query($sqry);
      $serieslist = array();
      while ($row = $sres->fetchArray()) {
          $serieslist[$row['id']] = $row['name'];
      }
      $done = false;
      foreach ($serieslist as $key => $seriesname) {
        if ($seriesname == $ebook->getSeriesName()) {
          $ebook->seriesId = $key;
          $this->update(['series' => $key,
                         'volume' => $ebook->getSeriesVolume()],
                        'books', ['md5id' => ['=', $ebook->id]]);
          $done = true;
        }
      }
      if (!$done) {
        $this->insert(['name' => ['string', $ebook->getSeriesName()]], 'series');
        $key = $this->db->querySingle("SELECT id from series WHERE name = '" . $ebook->getSeriesName() . "'");
        $upd = $this->update(['series_id' => $key,
                       'series_volume' => $ebook->getSeriesVolume()],
                       'books', ['md5id' => ['=', $ebook->id]]);
        $success = $success && $upd;
      }
    }
    return $success;
  }

  /**
   * set Busy flag and job
   * @param string $job job that keeps busy
   */
  public function setBusy($job) {
    $this->update(['busy' => ['bool', true], 'job' => ['string', $job]],
                  'busy_flag');
  }

  /**
   * unset busy flag
   */
  public function setFree() {
    $this->update(['busy' => ['bool', false], 'job' => ['string', '']],
                  'busy_flag');
  }

  /**
   * if busy flag is set, return job
   * @return bool|string job or false
   */
  public function isBusy() {
    $qry = "select job from busy_flag where busy = 1";
    $res = $this->db->querySingle($qry);
    return ($res) ? $res : false;
  }

  /**
   * build and execute update query.
   * @param  array  $fields [name => [type, value]]
   * @param  string $table  table
   * @param  array  $where  [name => [operator, value]]
   * @return boolean        success
   */
  public function update($fields, $table, $where = null) {
    $qry = "UPDATE $table SET ";
    foreach ($fields as $name => $record) {
      [$type, $value] = $record;
      switch ($type) {
        case 'num':
          $updates[] = "$name = $value";
          break;
        case 'bool':
          $updates[] = ($value) ? "$name = 1" : "$name = 0";
          break;
        case 'string':
        case 'date':
          $value = \SQLite3::escapeString($value);
          $updates[] = "$name = '$value'";
          break;
        default:
          $value = \SQLite3::escapeString($value);
          $updates[] = "$name = '$value'";
      }
    }
    $qry .= \implode(', ', $updates);
    if ($where) {
      foreach ($where as $name => $condition) {
        [$operator, $value] = $condition;
        if (\is_string($value)) {
          $value = "'" . SQLite3::escapeString($value) . "'";
        }
        $whereCondition[] = "$name $operator $value";
      }
      $qry .= " WHERE " . \implode(' AND ', $whereCondition);
    }
    $result = $this->db->exec($qry);
    $this->logThis($qry, self::DEBUG);
    return $result;
  }

  /**
   * build and execute update query.
   * @param  array  $fields  [name => [type, value]]
   * @param  string $table   table
   * @param  bool   $replace or replace
   * @return boolean         success
   */
  public function insert($fields, $table, $replace = false) {
    $qry = ($replace) ? "INSERT OR REPLACE INTO $table " : "INSERT INTO $table ";
    $i = 0;
    foreach ($fields as $name => $record) {
      [$type, $value] = $record;
      $which[$i] = $name;
      switch ($type) {
        case 'num':
          $values[$i] = "$value";
          break;
        case 'bool':
          $values[$i] = ($value) ? 1 : 0;
          break;
        case 'string':
        case 'date':
          $values[$i] = \SQLite3::escapeString($value);
          break;
        default:
          $values[$i] = \SQLite3::escapeString($value);
      }
      $i++;
    }
    $qry .= '(' . \implode(', ', $which) . ') ';
    $qry .= 'VALUES (' .  \implode(', ', $values) . ')';
    $result = $this->db->exec($qry);
    if ($table != 'activitylog') $this->logThis($qry, self::DEBUG);
    return $result;
  }

  /**
   * General select
   * @param  array  $fields [description]
   * @param  array  $tables ['table', 'optional join condition']
   * @param  array  $where  ['field' => ['operator', 'value']]
   * @param  string $group  group by
   * @param  string $order  order by
   * @param  int    $limit  limit
   * @param  bool   $single return single value
   * @return array|string   result
   */
  public function select($fields, $tables, $where, $group = false,
                         $order = false, $limit = false, $single = false) {
    $qry = "SELECT " . implode(', ', $fields) . ' ';
    $from = '';
    foreach($tables as $tabledef) {
      [$table, $joinCondition] = $tabledef;
      if (!$joinCondition) {
        $from = "FROM $table ";
      } else {
        $from .= " JOIN $table ON $joinCondition";
      }
    }
    $qry .= $from;
    if ($where) {
      foreach ($where as $name => $condition) {
        [$operator, $value] = $condition;
        if (\is_string($value)) {
          $value = "'" . SQLite3::escapeString($value) . "'";
        }
        $whereCondition[] = "$name $operator $value";
      }
      $qry .= " WHERE " . \implode(' OR ', $whereCondition);
    }
    if ($group) {
      $qry .= " GROUP BY $group";
    }
    if ($order) {
      $qry .= " ORDER BY $order";
    }
    if ($limit) {
      $qry .= " LIMIT $limit";
    }
    if ($single) {
      $res = $this->db->querySingle($qry);
      $result = ($res) ? $res : false;
    } else {
      $this->logThis($qry, 2);
      $query = $this->db->query($qry);
      $result = [];
      while ($row = $query->fetchArray()) {
        $result[] = $row;
      }
    }
    $this->logThis($qry, self::DEBUG);
    return $result;
  }

}
