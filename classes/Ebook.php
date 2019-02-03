<?php
namespace EBookLib;


use EBookLib\Epub\CoverData as CoverData;
use EBookLib\Epub\Spine as Spine;
use EBookLib\Epub\Manifest as Manifest;
use EBookLib\Epub\Metadata as EpubMetadata;
use EBookLib\Epub\Guide as Guide;
use EBookLib\Epub\TableOfContents;
use EBookLib\Epub\TocItem;
use Treinetic\ImageArtist\lib\Image as Image;
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
   * @var Manifest
   */
  public $manifest;

  /**
   * table of contents
   * @var TableOfContents
   */
  public $toc;

  /**
   * Spine (all files)
   * @var Spine
   */
  public $spine;

  /**
   * Guide
   * @var Guide
   */
  public $guide;

  /**
   * Metadata
   * @var EpubMetadata
   */
  public $metadata;

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
      $rootfile = $this->get_metafile($zip);
      $path = dirname($rootfile);
      $this->path = ($path != '.') ? $path . '/':'';
      $dom = new \DOMDocument();
      $dom->loadXML($zip->getFromName($rootfile));
      $package = $dom->getElementsByTagName('package')->item(0);
      $uniqueid = $package->getAttribute('unique-identifier');
      $version = $package->getAttribute('version');
      $meta = $dom->getElementsByTagName('metadata')->item(0);
      // legacy
      $this->allmeta = $meta;

      // Testcode
      $this->spine = new Spine($dom);
      $this->manifest = new Manifest($dom);
      $this->metadata = new EpubMetadata($dom);
      $this->guide = new Guide($dom);
      $tocdom = new \DOMDocument();
      $ncxpath = $this->path.$this->manifest->getItem('ncx')->href;
      $tocdom->loadXML($zip->getFromName($ncxpath));
      $this->toc = new TableOfContents($tocdom);
      // set members
      $this->title   = $this->metadata->getTitle();
      $this->author  = implode(', ', $this->metadata->getAuthors());
      $this->sortauthor = strtolower($this->author);
      $this->tags = $this->metadata->getSubjects();
      $this->summary = $this->metadata->getSummary();
      $this->series = $this->metadata->getSeries();
      //modify
      if (!$this->id) $this->create_id();
      $zip->close();
      return $this;
    }else{
      error_log('Opening Book zip ' . $epub . ' / ' . $filepath . ' failed');
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
    if (strpos('#', $idref) !== false) {
      list($idref, $position) = explode('#', $idref);
    }
    $href = $this->manifest->getItem($idref)->href;
    $navpoint = $this->toc->getById($idref);
    if (!$href) {
      $href = $navpoint->contentSrc;
    }
    if (isset($href)){
      $zip = new \ZipArchive;
      if ($zip->open($this->getFullFilePath())===TRUE){
        $html = $zip->getFromName($this->path.$href);
      }
      $html = $this->injectStyle($html);
      $html = $this->injectBookTitle($html);
      $html = $this->fixImages($html, $navpoint);
      return $this->injectNavigation($html, $navpoint);
    }
  }

  /**
   * get cover.
   * @param bool $binary
   * @return mixed
   */
  public function getCover($binary = false) {
    $coverdata = new CoverData($this);
    $coverpath = $coverdata->coverPath;
    if ($binary) {
      $data = base64_encode($coverdata->getCoverImageData());
    }
    if (!$data) $data = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lE".
                        "QVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII=";
    return ($binary) ? "data:image/jpeg;base64,$data" : $this->path.$coverpath;
  }

  /**
   * Inject navigation links into chapter text
   * @param  string  $html
   * @param  TocItem $navpoint
   * @return string
   */
  public function injectNavigation($html, $navpoint) {
    $tochead = $this->getFormattedToc() . $this->getNextPrev($navpoint);
    $html = str_replace('<body>', $tochead, $html);
    $html = str_replace('</body>', $this->getNextPrev($navpoint).'</body>', $html);
    return $html;
  }

  /**
   * Inject navigation links into chapter text
   * // TODO: implement later
   * @param  string  $html
   * @param  TocItem $navpoint
   * @return string
   */
  public function fixImages($html, $navpoint) {
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
    foreach($this->toc->navpoints as $navpoint) {
      $ret .= "<li><a href='$baseurl/read/".$this->id."/".$navpoint->id ."'> ";
      $ret .= $navpoint->navLabel . "</a></li>";
    }
    $ret .= "</ul>";
    return $ret;
  }

  /**
   * // TODO: fix this for non-numeric playOrder.
   * @param TocItem $navpoint current url
   * @param string  $baseurl     base url
   * @return string
   */
  public function getNextPrev($navpoint, $baseurl = '/index.php') {
    $prev = $this->toc->getByPlayOrder($navpoint->playOrder - 1);
    $prevposition = '';
    if (strpos('#',$prev->contentSrc) !== 0) {
      list($href, $prevposition) = \explode('#', $prev->contentSrc);
      $prevposition = '#' . $prevposition;
    }
    $next = $this->toc->getByPlayOrder($navpoint->playOrder + 1);
    $nextposition = '';
    if (strpos('#',$next->contentSrc) !== 0) {
      list($href, $nextposition) = \explode('#', $next->contentSrc);
      $nextposition = '#' . $nextposition;
    }
    if ($prev){
      $link[] = "<a href='$baseurl/read/".$this->id."/".$prev->id."$prevposition'>Previous Chapter</a>";
    }
    $link[] = "<a href='$baseurl/show/".$this->id."/'>Index</a>";
    if ($next){
      $link[] = "<a href='$baseurl/read/".$this->id."/".$next->id."$nextposition'>Next Chapter</a>";
    }
    return '<div class="nextprev">'.implode(' | ', $link) . '</div>';
  }


  /**
   * @return string
   */
  public function modify_meta() {
    $this->metadata->setAuthors(explode(', ', $this->author));
    $this->metadata->setSubjects($this->tags);
    $this->metadata->setTitle($this->title);
    $this->metadata->setSummary($this->summary);
    $zip = new \ZipArchive;
    if ($zip->open($this->getFullFilePath()) === TRUE) {

      $fileToModify = $this->get_metafile($zip);
      $rootfile = $this->get_metafile($zip);
      $path = dirname($rootfile);
      $this->path = ($path != '.') ? $path . '/':'';
      $dom = new \DOMDocument();
      $dom->loadXML($zip->getFromName($rootfile));
      $package = $dom->getElementsByTagName('package')->item(0);
      $uniqueid = $package->getAttribute('unique-identifier');
      $version = $package->getAttribute('version');
      $newdom = new \DOMDocument('1.0');
      $root = $newdom->createElementNS('http://www.idpf.org/2007/opf','package');
      $root->setAttribute('unique-identifier', $uniqueid);
      $root->setAttribute('version', $version);
      $this->spine->writeElement($root, $newdom);
      $this->manifest->writeElement($root, $newdom);
      $this->metadata->writeElement($root, $newdom);
      $this->guide->writeElement($root, $newdom);
      $newdom->appendChild($root);
      $newdom->formatOutput = true;
      $newdom->preserveWhiteSpace = false;

      $newContents = $newdom->saveXML();
      //Delete the old...
      $zip->deleteName($fileToModify);
      //Write the new...
      $zip->addFromString($fileToModify, $newContents);
      //And write back to the filesystem.
      $zip->close();
      return "ok";
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

  /**
   * Update Cover
   * @param  bool $generate generate new cover
   * @return bool           success
   */
  public function updateCover($generate = false) {
    $tmp = dirname(__DIR__) . '/tmp/';
    $zip = new \ZipArchive();
    if (!$zip->open($this->getFullFilePath())===TRUE){
      return;
    }
    $coverdata = new CoverData($this);
    if (!$coverdata->coverId || $generate) {
      $covergen = COVERGEN . ' -a "' . $this->author . '" ';
      $covergen .= ' -t "' . $this->title . '" ';
      $covergen .= ' -o ' . $tmp . '/cover.png';
      exec($covergen, $out, $return);
      if ($return != 0){
        $zip->close();
        $this->library->logThis("Cover generate error: $covergen " . implode(',', $out));
        return;
      }
      if (!$generate) {
        file_put_contents($tmp.'cover.png', $coverdata->getCoverImageData());
      }
      $coverdata->setCover($tmp . 'cover.png');
    }
    if (file_exists($tmp . "illu.jpg") ) {
      $cover = new Image($tmp . "cover.png");
      $illu = new Image($tmp . "illu.jpg");
      if ($illu->getWidth() > $illu->getHeight()) {
        $illu->scaleToHeight(1600);
      } else {
        $illu->scaleToWidth(1600);
      }
      $illu->crop(0,0,1600,1600);
      $cover->merge($illu, 0, 800);
      $cover->save($tmp . 'covernew.png', IMAGETYPE_PNG);
      unlink("$tmp/illu.jpg");
      $coverdata->setCover($tmp . 'covernew.png');
      unlink($tmp . 'covernew.png');
    }
    if (\file_exists($tmp . 'cover.png')) unlink($tmp . 'cover.png');
    $this->modify_meta();
  }

  /**
   * get a file's content from ebook zip
   * @param  string $href href in zip
   * @return string       data
   */
  public function getFromZip($href) {
    $zip = new \ZipArchive();
    if (!$zip->open($this->getFullFilePath())===TRUE){
      return;
    }
    return $zip->getFromName($this->path.$href);
  }

  /**
   * write data to ebook zip
   * @param  string $filename file path
   * @param  string $data     data
   * @return bool           success
   */
  public function writeToZip($filename, $data) {
    $zip = new \ZipArchive();
    if ($zip->open($this->getFullFilePath())===TRUE){
      $zip->deleteName($this->path.$filename);
      $zip->addFromString($this->path.$filename, $data);
      $zip->close();
      return true;
    }
    return false;
  }
}
