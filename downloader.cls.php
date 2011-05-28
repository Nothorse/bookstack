<?php
require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/commandline.cls.php");
require_once(dirname(__FILE__) . "/ebook.cls.php");
require_once(dirname(__FILE__) . "/library.cls.php");
require_once(dirname(__FILE__) . "/epubgen/EPub.php");
require_once(dirname(__FILE__) . "/epubgen/lib.uuid.php");
require_once(dirname(__FILE__) . "/epubgen/Zip.php");
#require_once(__DIR__ . "/epubgen/EpubChapterSplitter.php");
$path = '/usr/lib/php';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

abstract class Downloader {
  protected $chapters = array();
//   protected $rawchapters = array();
  protected $author;
  protected $created;
  protected $updated;
  protected $title;
  protected $summary;
  protected $publisher = array('name' => '', 'url' => '');
  protected $subject = array();
  protected $baseurl;
  
  
  public function main($bookurl) {
    $raw = $this->retrieveBook($bookurl);
    $this->extractMetadata($raw);
    $this->getChapters($this->baseurl);
    $this->buildBook();
  }
  
  abstract public function retrieveBook($url);
  
  abstract protected function extractMetadata($data); 
  
  protected function getChapters($baseurl); {
    foreach($this->chapters as $name => $url) {
      $dom = new DomDocument();
      $dom->loadHTMLFile($baseurl.$url);
      $chaptext = $this->extractChaptertext($dom);
      $x = new DOMImplementation();
      $document = $x->createDocument(null, 'html',
          $x->createDocumentType("html", 
              "-//W3C//DTD XHTML 1.0 Transitional//EN", 
              "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"));
      $document->formatOutput = true;
      
      $html = $document->documentElement;
      $head = $document->createElement('head');
      $title = $document->createElement('title');
      $text = $document->createTextNode($this->title . ': ' . $name);
      $body = $document->createElement('body');
      
      $title->appendChild($text);
      $head->appendChild($title);
      $html->appendChild($head);
      $body->appendChild($document->importNode($chaptext, true));
      $html->appendChild($body);
      
      $this->chapters[$name] = $document->saveXML();
    }
  }

  abstract protected function extractChaptertext($chapdom);
  
  private function buildBook($storyurl) {
    $book = new EPub();
    $book->setTitle($this->title);
    $book->setIdentifier($storyurl, EPub::IDENTIFIER_URI);
    $book->setDescription($this->summary);
    $book->setAuthor($this->author, $this->author);
    $book->setPublisher($this->publisher['name'], $this->publisher['url']);
    $book->setDate(time());
    $book->setRights("Copyright by " . $this->author . ", freely shared.");
    $book->setSourceURL($storyurl);
    $i = 1;
    foreach($this->chapters as $chtitle => $chtext) {
      $book->addChapter(htmlentities("$chtitle", ENT_QUOTES, 'UTF-8', false), "chapter$i.xhtml", $chtext);
      $i++;
    }
    $book->setLanguage('en');
    $book->setSubject(implode(', ', $this->subject));
    $book->finalize();
    $file = $this->title .".epub";
    file_put_contents($file, $book->getBook());
    if($file) {
      $newbook = new ebook($file);
      $newbook->file = $newbook->cleanupFile($file);
      echo $newbook->title . ' moved to '.$newbook->file . "\n";
      $lib = new library();
      $lib->insertBook($newbook);
    } else {
      echo "No ebook given.\n";
    }
  }

}

?>