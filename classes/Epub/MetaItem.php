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
    $name = $element->getAttribute('name');
    $content = $element->getAttribute('content');
    if ($name && $content) {
      return new MetaItem($name, $content);
    }
    return false;
  }

  /**
   * is cover entry
   * @return bool
   */
  public function isCover() {
    return $this->name == 'cover';
  }

  /**
   * get content
   * @return string content of meta tag
   */
  public function getContent() {
    return $this->content;
  }

  /**
   * Setter
   * @param string $content
   */
  public function setContent(string $content) {
    $this->content = $content;
  }

  /**
   * is part of a series
   * @return bool
   */
  public function isSeries() {
    return $this->name == 'calibre:series';
  }

  /**
   * is a volume of a series
   * @return bool
   */
  public function isSeriesVolume() {
    return $this->name == 'calibre:series_index';
  }

}
