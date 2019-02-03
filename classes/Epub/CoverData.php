<?php
namespace EBookLib\Epub;

use EBookLib\Template;
use EBookLib\Ebook;

class CoverData {

  /**
   * ebook
   * @var Ebook
   */
  private $ebook;
  /**
   * imagepath
   * @var string
   */
  public $coverPath;

  public $coverId;

  public $coverpage;

  public $coverpageId;

  /**
   * Constructor
   * @param EbookLib/Ebook $ebook meta
   */
  public function __construct($ebook) {
    $this->ebook = $ebook;
    $this->coverId = $ebook->metadata->getCover();
    $this->coverPath = $ebook->manifest->getItem($this->coverId)->href;
  }

  /**
   * minimal cover html template
   * @return string html
   */
  private function getCoverHtml() {
    $tpl = new Template('cover');
    return $tpl->render(['title' => $this->ebook->title]);
  }

  /**
   * set a new Cover
   * @param string $coverPath path to cover outside zip
   */
  public function setCover($coverPath) {
    if (!$this->coverId) {
      $this->coverId = 'cover';
    }
    if (!$this->ebook->metadata->getCover()) {
      $this->ebook->metadata->setCover($this->coverId . 'image');
      $this->coverPath = 'Images/cover.png';
      $this->ebook->writeToZip('cover.xhtml', $this->getCoverHtml());
    }
    if (!$this->ebook->manifest->getItem($this->coverId)) {
      $this->ebook->manifest->setItem($this->coverId, 'application/xhtml+xml',
                                      $this->ebook->path . 'cover.xhtml');
      $this->ebook->manifest->setItem($this->coverId .'image', 'image/png',
                                      $this->coverPath);
    } else {
      $this->coverPath = $this->ebook->manifest->getItem($this->coverId)->href;
    }
    $this->ebook->writeToZip($this->coverPath, \file_get_contents($coverPath));
    if (!$this->ebook->spine->getItem($this->coverId)) {
      array_unshift($this->ebook->spine->items, new SpineItem($this->coverId, true));
    }
    if (!$this->ebook->guide->getItem($this->coverId)) {
      array_unshift($this->ebook->guide->items,
                    new GuideItem($this->ebook->path . 'cover.xhtml',
                    $this->coverId, 'Cover'));
    }
  }

  /**
   * get binary data from cover image
   * @return string image data
   */
  public function getCoverImageData() {
    return $this->ebook->getFromZip($this->coverPath);
  }
}
