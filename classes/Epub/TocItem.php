<?php
namespace EBookLib\Epub;

class TocItem {

  public $navLabel;

  public $contentSrc;

  public $id;

  public $playOrder;

  public function __construct($navLabel, $contentSrc, $id, $playOrder) {
    $this->navLabel = $navLabel;
    $this->contentSrc = $contentSrc;
    $this->id = $id;
    $this->playOrder = $playOrder;
  }

  /**
   * parse
   * @param  \DOMElement $element [description]
   * @return TocItem          [description]
   */
  public static function parseElement($element) {
    $id = $element->getAttribute('id');
    $playOrder = $element->getAttribute('playOrder');
    $label = $element->getElementsByTagName('navLabel')->item(0)
                     ->getElementsByTagName('text')->item(0)->nodeValue;
    $contentSrc = $element->getElementsByTagName('content')->item(0)->getAttribute('src');
    return new TocItem($label, $contentSrc, $id, $playOrder);
  }
}
