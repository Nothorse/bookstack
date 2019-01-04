<?php
namespace EBookLib\Epub;

class CoverData {

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
    $html = "<html><head><title>Cover</title></head><body><div>";
    $html .= "<img title='cover' alt='cover' ";
    $html .= " style='width: auto; height:98%' margin: 1%' ";
    $html .= " src='" . $this->coverPath . "'>";
    $html .= "</div></body></html>";
    return $html;
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
      $this->ebook->metadata->setCover($this->coverId);
      $this->coverPath = 'Images/cover.png';
    }
    if (!$this->ebook->manifest->getItem($this->coverId)) {
      $this->ebook->manifest->setItem($this->coverId, 'image/png', $this->coverPath);
    } else {
      $this->coverPath = $this->ebook->manifest->getItem($this->coverId)->href;
    }
    $this->ebook->writeToZip($this->coverPath, \file_get_contents($coverPath));
    if (!$this->ebook->spine->getItem($this->coverId)) {
      array_unshift($this->ebook->spine->items, new SpineItem($this->coverId, true));
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
