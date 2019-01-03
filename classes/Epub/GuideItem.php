<?php
namespace EBookLib\Epub;

class GuideItem {

  /**
   * location
   * @var string
   */
  public $href;

  /**
   * type
   * @var string
   */
  public $type;

  /**
   * title
   * @var string
   */
  public $title;

  /**
   * Constructor
   * @param string $href  location
   * @param string $type  type (unique!)
   * @param string $title mediatype
   */
  public function __construct($href, $type, $title) {
    $this->href = $href;
    $this->type = $type;
    $this->title = $title;
  }

  /**
   * write to DOMDocument
   * @param  \DOMDocument $meta meta documnt
   * @return \DOMElement        itemref element
   */
  public function write($meta) {
    $element = $meta->createElement('reference');
    $element->setAttribute('href', $this->href);
    $element->setAttribute('type', $this->type);
    $element->setAttribute('title', $this->title);
    return $element;
  }

  /**
   * new Item from DOMElement
   * @param  \DOMElement $element guide element
   * @return GuideItem         new guideItem
   */
  public static function parseElement($element) {
    $item = new GuideItem(
      $element->getAttribute('href'),
      $element->getAttribute('type'),
      $element->getAttribute('title')
    );
    return $item;
  }

}
