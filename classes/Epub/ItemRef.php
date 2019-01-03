<?php
namespace EBookLib\Epub;

class ItemRef {

  /**
   * idref
   * @var string
   */
  public $idref;

  /**
   * linear
   * @var bool
   */
  public $linear;

  public function __construct($idref, $linear) {
    $this->idref = $idref;
    $this->linear = $linear == 'yes';
  }

  /**
   * write to DOMDocument
   * @param  \DOMDocument $meta meta documnt
   * @return \DOMElement        itemref element
   */
  public function write($meta) {
    $element = $meta->createElement('itemref');
    $element->setAttribute('idref', $this->idref);
    if ($this->linear) $element->setAttribute('linear', 'yes');
    return $element;
  }

  /**
   * return new itemref from domelement
   * @param  \DOMElement $element dome element
   * @return ItemRef              itemref
   */
  public static function parseElement($element) {
    return new ItemRef(
      $element->getAttribute('idref'),
      $element->getAttribute('linear')
    );
  }
}
