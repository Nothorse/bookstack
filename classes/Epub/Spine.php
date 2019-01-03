<?php
namespace EBookLib\Epub;

/**
 * Object class for epub spine with parse and serialize functions.
 */
class Spine {

  /**
   * toc attribute
   * @var string
   */
  public $toc = 'ncx';

  /**
   * item array string idref => boolean linear
   * @var array
   */
  public $items = [];

  /**
   * Get Spine from xml
   * @param \DOMDocument $dom DomDocument
   */
  public function __construct($dom) {
    $spineelement = $dom->getElementsByTagName('spine')->item(0);
    $this->toc = ($spineelement->getAttribute('toc')) ?: 'ncx';
    $itemlist = $spineelement->getElementsByTagName('itemref');
    for ($i = 0; $i < $itemlist->length; $i++) {
      $element = $itemlist->item($i);
      $this->items[] = ItemRef::parseElement($element);
    }
  }

  /**
   * Write Spine to xml
   * @param \DOMElement  $root root element
   * @param \DOMDocument $dom  DomDocument
   */
  public function writeElement($root, $dom) {
    $spineelement = $root->getElementsByTagName('spine');
    if ($spineelement->length > 0) {
      $root->removeChild($spineelement->item(0));
    }
    $spine = $dom->createElement('spine');
    $spine->setAttribute('toc', $this->toc);
    foreach ($this->items as $itemref) {
      $spine->appendChild($itemref->write($dom));
    }
    $root->appendChild($spine);
  }
}
