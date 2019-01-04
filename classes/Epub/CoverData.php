<?php
namespace EBookLib\Epub;

class CoverData {

  private $ebook;
  /**
   * imagepath
   * @var string
   */
  public $coverPath;

  private $coverId;

  private $coverpage;

  private $coverpageId;

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
   * @param string $coverPath path to cover in zip
   */
  public function setCover($coverPath) {
    $this->coverPath = $coverPath;
    $coverId = $this->metadata->getCover();
    if (!$coverId) {
      $coverId = 'cover';
      $this->metadata->setCover($coverId);
    }
    $existingCover = $this->manifest->getItem($coverId);
    $this->manifest->setItem($coverId, 'image/png', $coverPath);
    if (!$this->spine->getItem($coverId)) {
      array_unshift($this->spine->items, new SpineItem($coverId, true));
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
