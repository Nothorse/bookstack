<?php
namespace EBookLib\Epub;

class CoverData {

  /**
   * metadata
   * @var MetaData;
   */
  private $metadata;

  /**
   * Spine
   * @var Spine
   */
  private $spine;

  /**
   * Manifest
   * @var Manifest
   */
  private $manifest;

  /**
   * Guide
   * @var Guide
   */
  private $guide;

  /**
   * imagepath
   * @var string
   */
  private $coverimage;

  /**
   * Constructor
   * @param Metadata $metadata meta
   * @param Manifest $manifest manifest
   * @param Spine    $spine    spine
   * @param Guide    $guide    guide
   */
  public function __construct($metadata, $manifest, $spine, $guide) {
    $this->metadata = $metadata;
    $this->manifest = $manifest;
    $this->spine = $spine;
    $this->guide = $guide;
  }

  /**
   * minimal cover html template
   * @return string html
   */
  private function getCoverHtml() {
    $html = "<html><head><title>Cover</title></head><body><div>";
    $html .= "<img title='cover' alt='cover' ";
    $html .= " style='width: auto; height:98%' margin: 1%' ";
    $html .= " src='" . $this->coverimage . "'>";
    $html .= "</div></body></html>";
    return $html;
  }

  /**
   * set a new Cover
   * @param string $coverimage path to cover in zip
   */
  public function setCover($coverimage) {
    $this->coverimage = $coverimage;
    $coverId = $this->metadata->getCover();
    if (!$coverId) {
      $coverId = 'cover';
      $this->metadata->setCover($coverId);
    }
    $existingCover = $this->manifest->getItem($coverId);
    $this->manifest->setItem($coverId, 'image/png', $coverimage);
    if (!$this->spine->getItem($coverId)) {
      array_unshift($this->spine->items, new SpineItem($coverId, true));
    }
  }

}
