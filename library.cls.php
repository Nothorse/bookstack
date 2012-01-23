<?php
require_once(__DIR__ . "/config.php");

class library{
  
  private $db;

  public function __construct($db = null) {
    if (!$db) {
      $db = '/Users/'.USER.'/Library/Preferences/at.grendel.ebooklib.library.sqlite';
    }
    $this->db = $this->getdb($db);  
    $this->checkTables();
  }


  private function getdb($dbname = "library.sqlite") {
    $base=new SQLite3($dbname);
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
  
  private function checkTables() {
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
  }
  
  public function insertBook($ebook) {
    $qry = "insert into books (title, 
                               author, 
                               sortauthor, 
                               file, 
                               summary, 
                               md5id, 
                               added) 
                               values 
                   ('".sqlite_escape_string($ebook->title)."',
                    '".sqlite_escape_string($ebook->author)."', 
                    '".sqlite_escape_string($ebook->sortauthor)."', 
                    '".sqlite_escape_string($ebook->file)."', 
                    '".sqlite_escape_string($ebook->summary)."', 
                    '".sqlite_escape_string($ebook->id)."', 
                    '".time()."')";
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
  
  public function updateBook($ebook) {
    $qry = "update books
              SET  title = '".sqlite_escape_string($ebook->title)."', 
                  author = '".sqlite_escape_string($ebook->author)."', 
              sortauthor = '".sqlite_escape_string($ebook->sortauthor)."', 
                 summary = '".sqlite_escape_string($ebook->summary)."'
             WHERE md5id = '".sqlite_escape_string($ebook->id)."'"; 
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
  
  public function getBook($md5id) {
    $qry = "select * from books where md5id = '".$md5id."'";
    $res = $this->db->query($qry);
    $row = $res->fetcharray();
    $book = new ebook();
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
  
  public function getBooklist($order = 'added desc', $where = '', $limit = false) {
    $lwhere = urldecode($where); 
    $booklist = array();
    $limstr = ($limit) ? " LIMIT 30": '';
    $qry = "select * from books $lwhere order by $order $limstr";
    $res = $this->db->query($qry);
    while ($row = $res->fetchArray()) {
        $book = new ebook();
        $book->title = $row['title'];
        $book->author = $row['author'];
        $book->sortauthor = $row['sortauthor'];
        $book->file = $row['file'];
        $book->summary = $row['summary'];
        $book->id = $row['md5id'];
        $book->updated = $row['added'];
        $booklist[$book->sortauthor.$book->title] = $book;
    } 
    error_log("booklist where: $lwhere");
    return $booklist;
  } 
  
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
  
  public function getTagList($updatedtags = true) {
    $booklist = array();
    if(!$updatedtags) {
      $where = " where tag not like 'last update%'";
    }
    $qry = "select * from tags $where order by tag asc";
    $res = $this->db->query($qry);
    while ($row = $res->fetchArray()) {
      if(strlen($row['tag']) > 0) {
        $count = $this->db->querySingle("select count(bookid) from taggedbooks where tagid = '".$row['id']."'");
        $booklist[$row['tag']]['name'] = $row['tag'];
        $booklist[$row['tag']]['books'][] = $count; 
      }
    }  
    return $booklist;
  }
  
  public function getTaggedBooks($tag, $order = 'added desc') {
    $booklist = array();
    $qry = "select * from books join taggedbooks on taggedbooks.bookid = books.id join tags on tags.id = taggedbooks.tagid where tag = '$tag' order by $order";
    $res = $this->db->query($qry);
    while ($row = $res->fetchArray()) {
        $book = new ebook();
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
  
  public function deleteBook($book) {
    $bookid = $this->db->querySingle('select id from books where md5id =\''.$book->id."'");
    $this->db->exec("delete from books where id = '$bookid'");
    $this->db->exec("delete from taggedbooks where bookid = '$bookid'");
    rename(dirname($book->file), "/Users/".USER."/.Trash/".basename(dirname($book->file)));
  }
  
}
?>
