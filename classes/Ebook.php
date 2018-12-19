<?php
namespace EBookLib;
/**
 * Class Ebook
 */
class Ebook extends MetaBook {
  /**
   * path of container
   * @var string
   */
  const CONTAINER = 'META-INF/container.xml';

  /**
   * full meta xml
   * @var \DOMDocument
   */
  public $allmeta;

  /**
   * Manifest
   * @var array
   */
  public $manifest = [];

  /**
   * table of contents
   * @var array
   */
  public $toc = [];

  /**
   * Spine (all files)
   * @var array
   */
  public $spine = [];

  /**
   * misc meta
   * @var array
   */
  public $otherMeta = [];

  /**
   * lookup reverse meta
   * @var array
   */
  public $lookup = [];


  /**
   * ebook constructor.
   * @param string $epub epub file
   */
  public function __construct($epub = null) {
    if (file_exists($epub)) {
      $this->file = str_ireplace(BASEDIR . '/', '', $epub);
      return $this->get_meta($epub);
    } else {
      return $this;
    }
  }

  /**
   * get Metadata of epub
   * @param  string $epub File reference
   * @return Ebook        ebook with metadata filled in
   */
  public function get_meta($epub = null) {
    $filepath = ($epub) ?: $this->getFullFilePath();
    $zip = new \ZipArchive;

    if ($zip->open($filepath)===TRUE){
      $container = simplexml_load_string($zip->getFromName(Ebook::CONTAINER));
      //$rootfile = $container->rootfiles->rootfile['full-path'];
      $rootfile = $this->get_metafile($zip);
      $path = dirname($rootfile);
      $this->path = ($path != '.') ? $path . '/':'';
      $xml =  simplexml_load_string($zip->getFromName($rootfile), 'SimpleXMLElement', LIBXML_NSCLEAN)->children('http://www.idpf.org/2007/opf');
      $opf = $xml->metadata;
      $meta = $opf->children('http://purl.org/dc/elements/1.1/');
      $this->title   = (string) $meta->title[0];
      $this->author  = (string) $meta->creator[0];
      $this->sortauthor = strtolower($this->author);
      $this->tags = (array) $meta['subject'];
      $this->oldtaglist =  (array) $meta['subject'];
      $this->oldtaglist[] = 'gethere';
      $this->summary = (string) $meta->description[0];
      if ($this->summary == '') {
        $this->summary = "No summary for this book yet.";
      }
      $this->allmeta = $meta;
      //$this->cover = $this->is_cover($zip, $epub);
      $this->create_id();
      // test with DOM

      $dom = new \DOMDocument();
      $dom->loadXML($zip->getFromName($rootfile));
      $meta = $dom->getElementsByTagName('metadata')->item(0);
      $this->title = $meta->getElementsByTagName('title')->item(0)->nodeValue;
      $this->author = $meta->getElementsByTagName('creator')->item(0)->nodeValue;
      $summary = $meta->getElementsByTagName('description')->item(0);
      if ($summary) {
        $this->summary = $summary->nodeValue;
      } else {
        $this->summary = 'no summary';
      }
      $this->otherMeta = [];
      foreach ($meta->getElementsByTagName('meta') as $id => $node) {
        $this->otherMeta[$node->getAttribute('name')] = $node->getAttribute('content');
      }

      $taglist = $dom->getElementsByTagName('subject');
      //print_r($taglist);
      if (empty($taglist)) {
        $this->tags[] = 'untagged';
      }
      foreach($taglist as $id => $tagnode) {
        $this->tags[$id] = $tagnode->nodeValue;
      }
      while($taglist->length > 0) {
        $meta->removeChild($taglist->item(0));
      }
      foreach($this->tags as $id => $tag) {
        $meta->appendChild($dom->createElementNS('http://purl.org/dc/elements/1.1/', 'dc:subject', $tag));
      }
      $this->allmeta = $dom;
      $manifest = $dom->getElementsByTagName('manifest')->item(0);
      foreach($manifest->getElementsByTagName('item') as $id => $node) {
          $this->manifest[$node->getAttribute('id')] = $node->getAttribute('href');
          $this->lookup[$node->getAttribute('href')] = $node->getAttribute('id');
      }
      $spine = $dom->getElementsByTagName('spine')->item(0);
      foreach($spine->getElementsByTagName('itemref') as $id => $node) {
          $this->spine[$node->getAttribute('idref')] = $this->manifest[$node->getAttribute('idref')];
      }
      // toc
      $toc = new \DOMDocument();
      $toc->loadXML($zip->getFromName($this->path.$this->manifest['ncx']));
      $navlist = $toc->getElementsByTagName('navPoint');
      foreach($navlist as $id => $navpoint) {
        $label = $navpoint->getElementsByTagName('navLabel')->item(0)->getElementsByTagName('text')->item(0)->nodeValue;
        $src = $navpoint->getElementsByTagName('content')->item(0)->getAttribute('src');
        $this->toc[$label] = $src;
      }
      $zip->close();
      return $this;
    }else{
      error_log('Opening Book zip failed');
      return 'failed';
    }
  }

  /**
   * @param  \DOMDocument $dom
   * @return mixed
   */
  public function prettyprint($dom) {
      $dom->normalize();
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $outXML = $dom->saveXML();
      $dom->loadXML($outXML, LIBXML_NSCLEAN);
      return $dom;
  }

  /**
   * @param  \ZipArchive $zip
   * @return \SimpleXMLElement
   */
  public function get_metafile($zip) {
      $container = simplexml_load_string($zip->getFromName(Ebook::CONTAINER));
      $rootfile = $container->rootfiles->rootfile['full-path'];
      return $rootfile;
  }

  /**
   * @param  string $epub epub file
   * @param  string $bookdir base dir
   * @return mixed|null|string
   */
  public function cleanupFile($epub = null, $bookdir = BASEDIR) {
    if(!$epub) {
      $epub = $this->file;
    }
    $canonicaldir = $this->sanitize($this->author) .'/' . $this->sanitize($this->title);
    $canonicalname = $canonicaldir . '/' . basename($epub);
    if (dirname($epub) != $canonicaldir) {
      if (!file_exists($bookdir . '/' . $canonicaldir)) {
        mkdir($bookdir . '/' . $canonicaldir, 0755, true);
      }
      rename($epub, $bookdir . '/' . $canonicalname);
      return $canonicalname;
    }
    return $epub;
  }

  /**
   * get chapter from id.
   * @param  string $idref id in toc
   * @return string
   */
  public function getChapter($idref) {
    foreach($this->manifest as $id => $href) {
      if ($id == $idref) {
        $chapter = $href;
      }
    }
    if (isset($chapter)){
      $zip = new \ZipArchive;
      if ($zip->open($this->getFullFilePath())===TRUE){
        $html = $zip->getFromName($this->path.$chapter);
      }
      $html = $this->injectStyle($html);
      $html = $this->injectBookTitle($html);
      return $this->injectNavigation($html, $chapter);
    }
  }

  /**
   * get cover.
   * @param bool $binary
   * @return mixed
   */
  public function getCover($binary = false) {
    $coverpath = $this->manifest[$this->otherMeta['cover']];
    $zip = new \ZipArchive();
    if ($zip->open($this->getFullFilePath())===TRUE){
      $cover = $zip->getFromName($this->path.$coverpath);
    }
    if ($binary) {
      $data = base64_encode($cover);
    }
    return ($binary) ? "data:image/jpeg;base64,$data" : $this->path.$coverpath;
  }

  /**
   * Inject navigation links into chapter text
   * @param  string $html
   * @param  string $chapter
   * @return mixed
   */
  public function injectNavigation($html, $chapter) {
    $tochead = $this->getFormattedToc() . $this->getNextPrev($chapter);
    $html = str_replace('<body>', $tochead, $html);
    $html = str_replace('</body>', '</body>' .$this->getNextPrev($chapter), $html);
    return $html;
  }

  /**
   * @param  string $html
   * @return string
   */
  public function injectStyle($html) {
    return str_replace('</head>', '<link rel="stylesheet" href="/rsrc/ui.css" type="text/css" media="all" /></head>', $html);
  }

  /**
   * @param  string $html html code
   * @return string
   */
  public function injectBooktitle($html) {
    return str_replace('<title>', '<title>'.$this->title.' - ', $html);
  }

  /**
   * @param  string $baseurl base
   * @return string
   */
  public function getFormattedToc($baseurl = '/index.php') {
    $ret = "<body class='read'><ul class='toc'>\n";
    foreach($this->toc as $chaptername => $href) {
      $ret .= "<li><a href='$baseurl/read/".$this->id."/".$this->lookup[$href]."'>$chaptername</a></li>";
    }
    $ret .= "</ul>";
    return $ret;
  }

  /**
   * @param string $currenthref current url
   * @param string $baseurl     base url
   * @return string
   */
  public function getNextPrev($currenthref, $baseurl = '/index.php') {
    $current = null;
    $prev = null;
    $next = null;
    foreach($this->toc as $title => $href) {
      $current = $href;
      if($href == $currenthref) {
        break;
      }
      $prev = $href;
    }
    $current = '';
    foreach($this->toc as $title => $href) {
      $next = $href;
      if($current == $currenthref) {
        break;
      }
      $current = $href;
    }
    if (isset($prev)){
      $link[] = "<a href='$baseurl/read/".$this->id."/".$this->lookup[$prev]."'>Previous Chapter</a>";
    }
    $link[] = "<a href='$baseurl/show/".$this->id."/".$this->lookup[$href]."'>Index</a>";
    if (isset($next)){
      $link[] = "<a href='$baseurl/read/".$this->id."/".$this->lookup[$next]."'>Next Chapter</a>";
    }
    return '<div class="nextprev">'.implode(' | ', $link) . '</div>';
  }


  /**
   * @return string
   */
  public function modify_meta() {
    $zip = new \ZipArchive;
    if ($zip->open($this->file) === TRUE) {
      $fileToModify = $this->get_metafile($zip);
      //Read contents into memory
      $oldContents = $zip->getFromName($fileToModify);
      //Modify contents:
      $meta = $this->allmeta->getElementsByTagName('metadata')->item(0);
      $meta->getElementsByTagName('creator')->item(0)->nodeValue = $this->author;
      $meta->getElementsByTagName('title')->item(0)->nodeValue =  $this->title;
      $summary = $meta->getElementsByTagName('description')->item(0);
      if (!$summary) {
        $meta->appendChild($this->allmeta->createElementNS('http://purl.org/dc/elements/1.1/', 'dc:description', $this->summary));
      } else {
        $summary->nodeValue =  $this->summary;
      }
      //tags
      $taglist = $meta->getElementsByTagName('subject');
      while($taglist->length > 0) {
        $meta->removeChild($taglist->item(0));
      }
      foreach($this->tags as $id => $tag) {
        $meta->appendChild($this->allmeta->createElementNS('http://purl.org/dc/elements/1.1/', 'dc:subject', trim($tag)));
      }

      $newContents = $this->prettyprint($this->allmeta)->saveXML();
      //Delete the old...
      $zip->deleteName($fileToModify);
      //Write the new...
      $zip->addFromString($fileToModify, $newContents);
      //And write back to the filesystem.
      $zip->close();
      return "ok: $newContents";
    } else {
      return 'failed';
    }
  }

  /**
   * @param  string $string string to saniize
   * @return string
   */
  public function sanitize($string) {
    return str_replace(['/', ',', ';'], ['_', '_'], $string);
  }

}
