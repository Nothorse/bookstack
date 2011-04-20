#!/usr/sbin/php-cgi -Cq
<?php
date_default_timezone_set('Europe/Vienna');
/**
 * Needs to manually require the parent class, as it is called outside the weblication framework
 */
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/commandline.cls.php");
require_once(__DIR__ . "/ebook.cls.php");
require_once(__DIR__ . "/library.cls.php");
require_once(__DIR__ . "/epubgen/EPub.php");
require_once(__DIR__ . "/epubgen/lib.uuid.php");
require_once(__DIR__ . "/epubgen/Zip.php");
#require_once(__DIR__ . "/epubgen/EpubChapterSplitter.php");
$path = '/usr/lib/php';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

class getMcStories extends CommandLine {
  
  private $chapters = array();
  private $author;
  private $created;
  private $updated;
  private $title;
  private $summary;
  
  /**
   * initParams -- Defining all parameters
   */
  protected function initParams() {
    $this->setCommand('addbook');
    $this->addParam('-u:,--url:', 'URL', 'URL');
  }
  
  public function main() {
    $url = $this->getArgument('URL');  
    $storyurl = dirname($url) . "/";
    echo $storyurl . "\n";
    $dom = new DomDocument();
    $dom->formatOutput = true;
    $dom->normalize();
    $dom->loadHTMLFile($storyurl);
    $divs = $dom->getElementsByTagName('div');
    foreach($divs as $id => $chapterdiv) {
      $class = $chapterdiv->getAttribute('class');
      if($class == 'chapter') {
        $a = $chapterdiv->getElementsByTagName('a')->item(0);
        $this->chapters[$a->textContent] = $a->getAttribute('href');
      }
    }
    $meta = $dom->getElementsByTagName('h3');
    foreach($meta as $id => $headline) {
      $class = $headline->getAttribute('class');
      switch($class) {
        case 'byline':
          $this->author = $headline->getElementsByTagName('a')->item(0)->textContent;
          break;
        case 'title':
          $this->title = $headline->textContent;
          break;
        case 'dateline':
          list($which, $date) = explode(' ', $headline->textContent, 2);
          if($which == 'Added') {
            $this->created = date_create($date);
          } else {
            $this->updated = date_create($date);
          }
      }
    }
    $wholetext = $dom->getElementById('text');
    $this->summary = $wholetext->getElementsByTagName('p')->item(0)->textContent;
    $this->getChapters($storyurl);
    // create the epub
    $book = new EPub();
    $book->setTitle($this->title);
    $book->setIdentifier($baseurl, EPub::IDENTIFIER_URI);
    $book->setDescription($this->summary);
    $book->setAuthor($this->author);
    $book->setPublisher("Erotic Mind Control Stories Archive", "http://mcstories.com/");
    $book->setDate(time());
    $book->setRights("Copyright by " . $this->author . ", freely shared.");
    $book->setSourceURL($baseurl);
    $i = 1;
    foreach($this->chapters as $chtitle => $chtext) {
      $book->addChapter("$chtitle", "chapter$i.xhtml", $chtext);
      $i++;
    }
    $book->setLanguage('en');
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
  
  private function getChapters($baseurl) {
    foreach($this->chapters as $name => $url) {
      $dom = new DomDocument();
      $dom->loadHTMLFile($baseurl.$url);
      $chaptext = $dom->getElementById('text');
      $document = DOMImplementation::createDocument(null, 'html',
          DOMImplementation::createDocumentType("html", 
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

}


$s = new getMcStories();
$s->main();
