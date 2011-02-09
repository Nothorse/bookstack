<?php
class library{
  
  private $db;

  public function __construct($db = 'library.sqlite') {
    $this->db = $this->getdb($db);  
    $this->checkTables();
  }


  private function getdb($dbname = "library.sqlite") {
    $base=new SQLiteDatabase($dbname, 0666, $err);
    if ($err)
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
    if ($q->numRows()!=4) {
        if (!@$this->db->queryexec("
            CREATE TABLE books (
                id INTEGER NOT NULL PRIMARY KEY,
                title VARCHAR ( 255 ) NOT NULL,
                author VARCHAR(255) NOT NULL,
                sortauthor VARCHAR(255) NOT NULL,
                file VARCHAR(255) NOT NULL,
                summary TEXT,
                md5id varchar(255) NOT NULL,
                added timestamp NOT NULL
                )")
        ) exit ("Create SQLite Database Error\n");
    }
  }
  
  public function insertBook($ebook) {
    
  }
  
  public function getBook($md5id) {
  }
  
  public function getBooklist($order = 'added') {
  
  }  
}

$a = new library();
?>
