<?php
namespace EBookLib\Epub;

class ManifestItem {

  /**
   * location
   * @var string
   */
  public $href;

  /**
   * id
   * @var string
   */
  public $id;

  /**
   * mediatype
   * @var string
   */
  public $mediatype;

  /**
   * Constructor
   * @param string $href     location
   * @param string $id       id (unique!)
   * @param string $mediatype mediatype
   */
  public function __construct($href, $id, $mediatype) {
    $this->href = $href;
    $this->id = $id;
    $this->mediatype = $mediatype;
  }

  /**
   * write to DOMDocument
   * @param  \DOMDocument $meta meta documnt
   * @return \DOMElement        itemref element
   */
  public function write($meta) {
    $element = $meta->createElement('item');
    $element->setAttribute('href', $this->href);
    $element->setAttribute('id', $this->id);
    $element->setAttribute('media-type', $this->mediatype);
    return $element;
  }

  /**
   * new Item from DOMElement
   * @param  \DOMElement $element manifest element
   * @return ManifestItem         new ManifestItem
   */
  public static function parseElement($element) {
    $item = new ManifestItem(
      $element->getAttribute('href'),
      $element->getAttribute('id'),
      $element->getAttribute('media-type')
    );
    return $item;
  }

}
