<?php
class ebook {
  const CONTAINER = 'META-INF/container.xml';
  public $author;
  public $sortauthor;
  public $title;
  public $summary;
  public $id;
  public $path;
  public $file;
  public $tags;
  public $allmeta;
  public $metaelements;
//   public $manifest;
//   public $spine;
  
  public function __construct($epub = null) {
    if (file_exists($epub)) {
      $this->file = $epub;
      return $this->get_meta($epub);
    } else {
      return $this;
    }
  }

  function create_id() {
    $this->id = md5('thcatgen'.$this->title.$this->author);
  }
  
  function get_thumb($sz = 75, $inline = false) {
    if(!file_exists(dirname($this->path)."/thumb-$sz.png")) {
      $img = imagecreatefromjpeg($this->cover);
      $srcw = imagesy($img);
      $srch = imagesx($img);
      $factor = ($srcw/$sz > $srch/$sz) ? $srcw/$sz : $srch/$sz;
      $dstw = round($srcw/$factor);
      $dsth = round($srch/$factor);
      $dst = imagecreatetruecolor($dsth, $dstw);
      imagecopyresampled($dst, $img, 0, 0, 0, 0, $dsth, $dstw, $srch, $srcw);
      imagepng($dst, dirname($this->path)."/thumb-$sz.png");
    }
    if ($inline) {
      return "data:image/png;base64," . base64_encode(file_get_contents(dirname($this->path)."/thumb-$sz.png"));
    } else {
      file_get_contents(dirname($this->path)."/thumb-$sz.png");
    }
  }
  
  function get_cover($inline = false) {
    if(!file_exists(dirname($this->path)."/cover-resized.png")) {
    $img = imagecreatefromjpeg($this->cover);
    $srcw = imagesy($img);
    $srch = imagesx($img);
    $factor = ($srcw/320 > $srch/480) ? $srcw/320 : $srch/480;
    $dstw = round($srcw/$factor);
    $dsth = round($srch/$factor);
    $dst = imagecreatetruecolor($dsth, $dstw);
    imagecopyresampled($dst, $img, 0, 0, 0, 0, $dsth, $dstw, $srch, $srcw);
    imagepng($dst, dirname($this->path)."/cover-resized.png");
    }
    if ($inline) {
      return "data:image/png;base64," . base64_encode(file_get_contents(dirname($this->path)."/cover-resized.png"));
    } else {
      file_get_contents(dirname($this->path)."/cover-resized.png");
    }
  }  
  /**
   * get Metadata of epub
   * @param  string $epub File reference
   * @return ebook        ebook with metadata filled in
   */
  function get_meta($epub) {
    $zip = new ZipArchive;
    if ($zip->open($epub)===TRUE){
      $container = simplexml_load_string($zip->getFromName(ebook::CONTAINER));
//       $rootfile = $container->rootfiles->rootfile['full-path'];
      $rootfile = $this->get_metafile($zip);
      $xml =  simplexml_load_string($zip->getFromName($rootfile), 'SimpleXMLElement', LIBXML_NSCLEAN)->children('http://www.idpf.org/2007/opf');
      $opf = $xml->metadata;
      $meta = $opf->children('http://purl.org/dc/elements/1.1/');
      $this->title   = (string) $meta->title[0];
      $this->author  = (string) $meta->creator[0];
      $this->sortauthor = strtolower($this->author);
      $this->tags = (array) $meta['subject'];
      $this->summary = (string) $meta->description[0];
      if ($this->summary == '') {
        $this->summary = "No summary for this book yet.";
      }
      $this->allmeta = $meta;
      //$this->cover = $this->is_cover($zip, $epub);
      $this->create_id();
      // test with DOM
      $dom = new DomDocument();
      $dom->loadXML($zip->getFromName($rootfile));
      $meta = $dom->getElementsByTagName('metadata')->item(0);
      foreach($meta->childNodes as $id => $node) {
        $this->metaelements[] = "$id: " . $node->nodeName .' -- '.$node->nodeValue;
      
      }
      $this->title = $meta->getElementsByTagName('title')->item(0)->nodeValue;
      $taglist = $dom->getElementsByTagName('subject');
      foreach($taglist as $id => $tagnode) {
        $this->tags[$id] = $tagnode->nodeValue;
      }
      while($taglist->length > 0) {
        $meta->removeChild($taglist->item(0));
      }
      $this->tags[] = 'fun';
      foreach($this->tags as $id => $tag) {
        $meta->appendChild($dom->createElementNS('http://purl.org/dc/elements/1.1/', 'dc:subject', $tag));
      }
      $dom->normalize();
      $dom->formatOutput = true;
      $this->allmeta = $dom->saveXML();
      $zip->close();
      return $this;
    }else{
      return 'failed';
    }
  }
  
  function get_metafile($zip) {
      $container = simplexml_load_string($zip->getFromName(ebook::CONTAINER));
      $rootfile = $container->rootfiles->rootfile['full-path'];
      return $rootfile;
  }
  
  function is_cover($zip, $epub) {
    // check for a cover in the Directory
    if (file_exists(dirname($epub).'/cover.jpg')) {
      return dirname($epub).'/cover.jpg';
    }
    // check inside the zipfile
    $zipindex = $zip->locateName('_cover_.jpg', ZIPARCHIVE::FL_NOCASE|ZIPARCHIVE::FL_NODIR);
    if ($zipindex !== false) {
      file_put_contents(dirname($epub).'/cover.jpg', $zip->getFromIndex($zipindex));
      return dirname($epub).'/cover.jpg';
    }
    if (file_exists(dirname(dirname($epub)) .'/cover.jpg')) {
      return dirname(dirname($epub)).'/cover.jpg';
    }
    return 'defaultcover.jpg';
  }
  
  function cleanupFile($epub = null, $bookdir = "/Users/thomas/Books") {
    if(!$epub) {
      $epub = $this->file;
    }
    $canonicaldir = $bookdir . '/' . $this->sanitize($this->author) .'/' . $this->sanitize($this->title);
    $canonicalname = $canonicaldir . '/' . basename($epub);
    if (dirname($epub) != $canonicaldir) {
      mkdir($canonicaldir, 0755, true);
      rename($epub, $canonicalname);
      return $canonicalname;
    }
    return $epub;
  }
  
  function extract_chapter($idref) {
//     foreach($this->manifest as $id => $item) {
//       if ($item['id'] == $idref) {
//         $chapter = $item['href'];
//       }
//     }
//     if (isset($chapter)){
//       $zip = new ZipArchive;
//       if ($zip->open($this->path)===TRUE){
//         return $zip->getFromName($chapter);
//       }
//     }
  }
  
  function modify_meta() {
    $zip = new ZipArchive;
    if ($zip->open($this->file) === TRUE) {
      //Read contents into memory
      $oldContents = $zip->getFromName($fileToModify);
      //Modify contents:
      $newContents = str_replace('key', $_GET['param'], $oldContents);
      //Delete the old...
      $zip->deleteName($fileToModify);
      //Write the new...
      $zip->addFromString($fileToModify, $newContents);
      //And write back to the filesystem.
      $zip->close();
      echo 'ok';
    } else {
      echo 'failed';
    }
  }
  
  function sanitize($string) {
    return $string;
      //return ereg_replace('[^A-Za-z0-9- ]', '', $string);
  }
  
  function edit_book($key, $val) {
    
  }
  
  function trunc_summary($char) {
    $ct = strlen($this->summary);
    if ($ct>$char) {
      return substr($this->summary, 0, 100) . "...";
    }
    return $this->summary;
  }
  
  public function __toString() {
    return basename($this->file);
  }
  
}
