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
    return $book;
  }
  
  public function getBooklist($order = 'added desc', $where = '') {
    $booklist = array();
    $qry = "select * from books $where order by $order";
    $res = $this->db->query($qry);
    while ($row = $res->fetchArray()) {
        $book = new ebook();
        $book->title = $row['title'];
        $book->author = $row['author'];
        $book->sortauthor = $row['sortauthor'];
        $book->file = $row['file'];
        $book->summary = $row['summary'];
        $book->id = $row['md5id'];
        $booklist[$book->sortauthor.$book->title] = $book;
    }  
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
}
?>
