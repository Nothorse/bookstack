<?php
namespace EBookLib\Epub;

/**
 * NCX navpoint item
 */
class TocItem {

  /**
   * navpoint label
   * @var string
   */
  public $navLabel;

  /**
   * href of file in zip
   * @var string
   */
  public $contentSrc;

  /**
   * idref
   * @var string
   */
  public $id;

  /**
   * play order of navpoints
   * @var string
   */
  public $playOrder;

  /**
   * Constructor
   * @param string $navLabel   label
   * @param string $contentSrc href
   * @param string $id         idref
   * @param string $playOrder  play order
   */
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
