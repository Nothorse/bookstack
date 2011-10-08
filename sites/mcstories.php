<?php
require_once(dirname(__DIR__) . "/config.php");
require_once(dirname(__DIR__) . "/downloader.php");
$path = '/usr/lib/php';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

class McStories extends Downloader {

  public function _construct() {
    $this->publisher = array("Erotic Mind Control Stories Archive",     
                             "http://mcstories.com/");
    $this->subject = 'Erotica, Mind Control';
  }

  protected function getIndex($url, $adultcheck) {
    $storyurl = dirname($url) . "/";
    echo $storyurl . "\n";
    $dom = new DomDocument();
    $dom->formatOutput = true;
    $dom->normalize();
    $dom->loadHTMLFile($storyurl);
    return $dom;  
  } 

  protected function parseChapterlist($dom) {
    $chapters = array();
    $divs = $dom->getElementsByTagName('div');
    foreach($divs as $id => $chapterdiv) {
      $class = $chapterdiv->getAttribute('class');
      if($class == 'chapter') {
        $a = $chapterdiv->getElementsByTagName('a')->item(0);
        $chapters[$a->textContent] = $a->getAttribute('href');
      }
    }
    if(empty($this->chapters)) {
      $links = $dom->getElementsByTagName('a');
      foreach($links as $id => $a) {
        $url = $a->getAttribute('href');
        if(strpos($url, '..') === false && strpos($url, 'http://') === false) {
          $chapters[$a->textContent] = $a->getAttribute('href');
        }
      }
    }
    if(empty($this->chapters)) {
      exit("No chapters found\n");
    }
    return $chapters;
  }

  protected function getContent($dom);
  
  protected function getMetaData($dom) {
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
    if(!$this->created) {
      $this->created = $this->updated;
    }
    $wholetext = $dom->getElementById('text');
    $this->summary = $wholetext->getElementsByTagName('p')->item(0)->textContent;
  }

  public function retrieveBook($url) {
    $this->getChapters($storyurl);
    // create the epub
    $book = new EPub();
    $book->setTitle($this->title);
    $book->setIdentifier($storyurl, EPub::IDENTIFIER_URI);
    $book->setDescription($this->summary);
    $book->setAuthor($this->author, $this->author);
    $book->setPublisher("Erotic Mind Control Stories Archive", "http://mcstories.com/");
    $book->setDate(time());
    $book->setRights("Copyright by " . $this->author . ", freely shared.");
    $book->setSourceURL($storyurl);
    $i = 1;
    foreach($this->chapters as $chtitle => $chtext) {
      $book->addChapter(htmlentities("$chtitle", ENT_QUOTES, 'UTF-8', false), "chapter$i.xhtml", $chtext);
      $i++;
    }
    $book->setLanguage('en');
    $book->setSubject('Erotica, Mind Control');
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



}

?>