<?php
require('uuid.cls.php');
class opdsdisplay {
  
  private $dom;// DomDocument();
  
  private $feed;
  
  const ATOM = 'http://www.w3.org/2005/Atom';
  
  private $selfurl;
  
  public function __construct() {
    $this->selfurl = 'http://'.$_SERVER["SERVER_NAME"] . ':' . $_SERVER["SERVER_PORT"] . $_SERVER['SCRIPT_NAME'];
    $this->dom = new DOMDocument('1.0', 'utf-8');
    $this->feed = $this->dom->createElementNS(self::ATOM, 'feed');
    $this->feed->appendChild($this->dom->createElementNS(self::ATOM, 'title', 'Giles Library'));
    $this->feed->appendChild($this->dom->createElementNS(self::ATOM, 'subtitle', 'Local Library served by Giles'));
    $this->feed->appendChild($this->dom->createElementNS(self::ATOM, 'updated', date('c')));
    $this->feed->appendChild($this->dom->createElementNS(self::ATOM, 'icon', '/favicon.ico')); 
    $author = $this->dom->createElementNS(self::ATOM, 'author');
    $author->appendChild($this->dom->createElementNS(self::ATOM, 'name', 'Giles Library'));
    $author->appendChild($this->dom->createElementNS(self::ATOM, 'uri', 'http://hacks.grendel.at'));
    $author->appendChild($this->dom->createElementNS(self::ATOM, 'name', 'giles@grendel.at'));
    $this->feed->appendChild($author);
    $this->feed->appendChild($this->dom->createElementNS(self::ATOM, 'id', 'urn:uuid:60a76c80-d399-12d9-b91C-0883939e0af6'));
    $link = $this->dom->createElementNS(self::ATOM, link);
    $link->setAttribute('href', $this->selfurl);
    $link->setAttribute('rel', 'self');
    $link->setAttribute('type', "application/atom+xml;profile=opds-catalog;kind=navigation");
    $this->feed->appendChild($link);
    $link = $this->dom->createElementNS(self::ATOM, link);
    $link->setAttribute('href', $this->selfurl);
    $link->setAttribute('rel', 'start');
    $link->setAttribute('type', "application/atom+xml;profile=opds-catalog;kind=navigation");
    $this->feed->appendChild($link);
    $entry = $this->dom->createElementNS(self::ATOM, 'entry');
    $entry->appendChild($this->dom->createElementNS(self::ATOM, 'title', 'Authors'));
    $entry->appendChild($this->dom->createElementNS(self::ATOM, 'updated', date('c')));
    $entry->appendChild($this->dom->createElementNS(self::ATOM, 'id', 'urn:giles:8D2B9F95-9C40-44B1-A3A6-E3AB3907B035'));
    $link = $this->dom->createElementNS(self::ATOM, 'link');
    $link->setAttribute('rel', 'subsection');
    $link->setAttribute('href', $this->selfurl . '/author/');
    $link->setAttribute('type', 'application/atom+xml;profile=opds-catalog;kind=acquisition');
    $entry->appendChild($link);
    $summary = $this->dom->createElementNS(self::ATOM, 'content', "Catalog by Author");
    $summary->setAttribute('type', 'text');
    $entry->appendChild($summary);
    $this->feed->appendChild($entry);
  }

  public function printBookList($list, $divid = 'list', $curid = null) {
    foreach ($list as $id => $book) {
      $entry = $this->dom->createElementNS(self::ATOM, 'entry');
      $entry->appendChild($this->dom->createElementNS(self::ATOM, 'title', $book->title));
      $entry->appendChild($this->dom->createElementNS(self::ATOM, 'updated', date($book->updated)));
      $entry->appendChild($this->dom->createElementNS(self::ATOM, 'id', 'urn:giles:'.$book->id));
      $author = $this->dom->createElementNS(self::ATOM, 'author');
      $author->appendChild($this->dom->createElementNS(self::ATOM, 'name', $book->author));
      $author->appendChild($this->dom->createElementNS(self::ATOM, 'uri', $this->selfurl . '/author/'.urlencode($book->author)));
      $entry->appendChild($author);
      $link = $this->dom->createElementNS(self::ATOM, 'link');
      $link->setAttribute('rel', 'http://opds-spec.org/acquisition/open-access');
      $link->setAttribute('href', $this->selfurl . '/get/'.$book->id.'/');
      $link->setAttribute('type', 'application/epub+zip');
      $entry->appendChild($link);
      $summary = $this->dom->createElementNS(self::ATOM, 'summary', $book->summary);
      $summary->setAttribute('type', 'text');
      $entry->appendChild($summary);
      $this->feed->appendChild($entry);
    }
    $this->dom->appendChild($this->feed);
    $this->dom->normalize();
    $this->dom->preserveWhiteSpace = false;
    $this->dom->formatOutput = true;
    //$dom = $this->prettyprint($this->dom);
    print $this->dom->saveXML();
  }
  
  public function printAuthorList($list, $what, $current= null) {
    foreach ($list as $id => $author) {
      $entry = $this->dom->createElementNS(self::ATOM, 'entry');
      $entry->appendChild($this->dom->createElementNS(self::ATOM, 'title', $author['name']));
      $entry->appendChild($this->dom->createElementNS(self::ATOM, 'updated', date('c')));
      $entry->appendChild($this->dom->createElementNS(self::ATOM, 'id', 'urn:giles:'.md5($author['name'])));
      $link = $this->dom->createElementNS(self::ATOM, 'link');
      $link->setAttribute('rel', 'subsection');
      $link->setAttribute('href', $this->selfurl . '/author/'.urlencode($author['name']));
      $link->setAttribute('type', 'application/atom+xml;profile=opds-catalog;kind=acquisition');
      $entry->appendChild($link);
      $summary = $this->dom->createElementNS(self::ATOM, 'content', "Works by ".$author['name']);
      $summary->setAttribute('type', 'text');
      $entry->appendChild($summary);
      $this->feed->appendChild($entry);
    }
    $this->dom->appendChild($this->feed);
    $this->dom->normalize();
    $this->dom->preserveWhiteSpace = false;
    $this->dom->formatOutput = true;
    //$dom = $this->prettyprint($this->dom);
    print $this->dom->saveXML();
  }
  
  public function getFormattedList($type = 'author') {
    $db = new library();
    $list = $db->getAuthorList();
    $formattedlist = "<ul>\n";
    foreach($list as $author => $rec) {
      $formattedlist .= "<li><a href=\"http://".SERVER.BASEURL."/$what/".$author['name']."/\">$author</a></li>\n";
    }
    $formattedlist .= '</ul>';
    return $formattedlist;
  }
  
  public function listTags() {
    $db = new library();
    $list = $db->getTagList(false);
    $taglist = '';
    foreach($list as $id => $tag) {
      $taglist .= "<li><a href=\"\">".$tag['name']."</a></li>";
    }
    return "<ul>$taglist</ul>";
  }
  
  public function printHeader() {
    header('Content-type: application/xml');
    /**
    print '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
<title>Book Catalog</title>
<updated>'.date('c').'</updated>
<author>
<name>Giles</name>
<uri>http://hacks.grendel.at/giles/</uri>
<email>giles@grendel.at</email>
</author>
<subtitle>
Local Library served by Giles
</subtitle>
<id>urn:uuid:60a76c80-d399-12d9-b91C-0883939e0af6</id>
<link rel="self" type="application/atom+xml" href="http://thbuch.local:3215/"/>
  ';
  **/
  }
  
  public function buildPage() {
  
  
  }
  
  public function showDetails($book, $protocol = 'http') {
    $geturl = "$protocol://".SERVER.BASEURL."/get/".$book->id.'/'.$book->title;
    $editurl = "http://".SERVER.BASEURL."/edit/".$book->id.'/'.$book->title;
    $deleteurl = "http://".SERVER.BASEURL."/delete/".$book->id.'/'.$book->title;
    $toc = $book->getFormattedToc("http://".SERVER.BASEURL);
    $details = <<<EOT
    <div id="details">
      $toc
      <h1>$book->title</h1>
      <h2>$book->author</h2>
      <p>$book->summary</p>
      <p><a href="$geturl">Download</a> | <a href="$editurl">Edit Metadata</a> | <a href="$deleteurl">Delete Book</a></p>
    </div>
EOT;
    return $details;
  }
  
  public function getEditform($book, $url) {
    $tags = implode(', ', $book->tags);
    $form = <<<EOT
  <div id="edit">
  <style type="text/css" title="text/css">
  <!--
  #edit {
    border: 2px #000 solid;
    padding: 3px;
  }
  
  form {
    width: 800px;
    position: relative;
  }
  label {
    font-size: 16px;
    font-weight: bold;
    display: block;
    line-height: 25px;
    margin: 0 0 5px 0;
    width: 800px;
    position:relative;
  }
  
  input, textarea {
    width: 700px;
    height: 25px;
    font-size: 16px;
    border: none;
    left:20px;
    position: relative;
    display: block;
  }
  
  textarea {
    height: 150px;
    line-height: 25px;
  }
  -->
  </style>
    <form action="$url" method="post">
      <label>Title: <input type="text" name="title" value="$book->title"></label>
      <label>Author: <input type="text" name="author" value="$book->author"></label>
      <label>Tags: <textarea name="tags">$tags</textarea></label>
      <label>Summary: <textarea name="summary">$book->summary</textarea>
      <button type="submit" id="submit" value="Update Book">Update Book</button>
    </form>
  </div>
EOT;
  return $form;
  }
  
  public function printFoot() {
    print '</feed>';
   }
   
   
  /**
   * prettyprint -- get rid of superfluous namespace declarations
   * @param  DOMDocument $dom
   * @return DOMDocument       return cleaned DOM
   */
  function prettyprint($dom) {
    $dom->normalize();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $outXML = $dom->saveXML(); 
    $dom->loadXML($outXML, LIBXML_NSCLEAN); 
    return $dom;
  }


}  
