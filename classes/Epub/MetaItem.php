<?php
namespace EBookLib\Epub;

/**
 * Meta tag
 */
class MetaItem {

  /**
   * name attribute
   * @var string
   */
  private $name;

  /**
   * content attribute
   * @var string
   */
  private $content;

  public function __construct($name, $content) {
    $this->name = $name;
    $this->content = $content;
  }

  /**
   * write to DOMDocument
   * @param  \DOMDocument $meta meta documnt
   * @return \DOMElement        itemref element
   */
  public function write($meta) {
    $element = $meta->createElement('meta');
    $element->setAttribute('name', $this->name);
    $element->setAttribute('content', $this->content);
    return $element;
  }

  /**
   * return new MetaItem from domelement
   * @param  \DOMElement $element dome element
   * @return MetaItem            item
   */
  public static function parseElement($element) {
    return new MetaItem(
      $element->getAttribute('name'),
      $element->getAttribute('content')
    );
  }
}
