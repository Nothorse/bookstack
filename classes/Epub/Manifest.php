<?php
namespace EBookLib\Epub;

class Manifest {

  /**
   * itemlist
   * @var ManifestItem[];
   */
  public $items = [];

  /**
   * Constructor
   * @param \DOMDocument $dom meta dom
   */
  public function __construct($dom) {
    $manifest = $dom->getElementsByTagName('manifest')->item(0);
    $itemlist = $manifest->getElementsByTagName('item');
    for ($i = 0; $i < $itemlist->length; $i++) {
      $element = $itemlist->item($i);
      $this->items[$element->getAttribute('id')] = ManifestItem::parseElement($element);
    }
  }

  /**
   * Write Manfest to xml
   * @param \DOMElement  $root root element
   * @param \DOMDocument $dom  DomDocument
   */
  public function writeElement($root, $dom) {
    $manifestelement = $root->getElementsByTagName('manifest');
    if ($manifestelement->length > 0) {
      $root->removeChild($manifestelement->item(0));
    }
    $manifest = $dom->createElement('manifest');
    foreach ($this->items as $item) {
      $manifest->appendChild($item->write($dom));
    }
    $root->appendChild($manifest);

  }

  /**
   * get manifest item by id
   * @param  string $id id of item
   * @return ManifestItem
   */
  public function getItem($id) {
    foreach ($this->items as $key => $item) {
      if ($item->id == $id) return $item;
    }
  }

  /**
   * set manifest item by id
   * @param  string $id        id of item
   * @param  string $mediatype mediatype
   * @param  string $href      location
   */
  public function setItem($id, $mediatype, $href) {
    $exists = false;
    foreach ($this->items as $key => $item) {
      if ($item->id == $id) {
        $item->href = $href;
        $item->mediatype = $mediatype;
        $this->items[$key] = $item;
        $exists = true;
      }
    }
    if (!$exists) {
      $this->items[] = new ManifestItem($href, $id, $mediatype);
    }
  }

}
