<?php
namespace EBookLib\Epub;

/**
 * Guide element
 */
class Guide {

  /**
   * possible types
   * @var array
   */
  private $types = ['cover', 'title-page', 'toc', 'index', 'glossary',
                    'acknowledgements', 'bibliography', 'colophon',
                    'copyright-page', 'dedication', 'epigraph', 'foreword',
                    'loi', 'lot', 'notes', 'preface', 'text'];

  /**
   * items
   * @var array
   */
  public $items = [];

  /**
   * constructor
   * @param \DOMDocument $dom dom
   */
  public function __construct($dom) {
    $guide = $dom->getElementsByTagName('guide')->item(0);
    if ($guide) {
      $itemlist = $guide->getElementsByTagName('reference');
      for ($i = 0; $i < $itemlist->length; $i++) {
        $element = $itemlist->item($i);
        $this->items[$element->getAttribute('id')] = GuideItem::parseElement($element);
      }
    }
  }

  /**
   * Write Guide to xml
   * @param \DOMElement  $root root element
   * @param \DOMDocument $dom  DomDocument
   */
  public function writeElement($root, $dom) {
    $guideelement = $root->getElementsByTagName('guide');
    if ($guideelement->length > 0) {
      $root->removeChild($guideelement->item(0));
    }
    $guide = $dom->createElement('guide');
    foreach ($this->items as $item) {
      $guide->appendChild($item->write($dom));
    }
    $root->appendChild($guide);

  }



}
