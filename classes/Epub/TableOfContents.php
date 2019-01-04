<?php
namespace EBookLib\Epub;

class TableOfContents {

  /**
   * metaItems
   * @see MetaItem
   * @var array
   */
  public $metaItems = [];

  public $navpoints = [];
  /**
   * Constructor
   * @param \DOMDocument $dom dom of ncx
   */
  public function __construct($dom) {
    $head = $dom->getElementsByTagName('head')->item(0);
    $metalist = $head->childNodes;
    for ($i = 0; $i < $metalist->length; $i++) {
      $element = $metalist->item($i);
      $tag = $element->nodeName;
      if ($tag == 'meta') {
        $this->metaItems[$element->getAttribute('name')] = MetaItem::parseElement($element);
      }
    }
    $toclist = $dom->getElementsByTagName('navPoint');
    for ($i = 0; $i < $toclist->length; $i++) {
      $element = $toclist->item($i);
      $this->navpoints[] = TocItem::parseElement($element);
    }
  }

  /**
   * get the navlist as naive array;
   * @return array [description]
   */
  public function getAsArray() {
    $navlist = [];
    foreach ($this->navpoints as $navpoint) {
      $navlist[$navpoint->navLabel] = $navpoint->contentSrc;
    }
    return $navlist;
  }

  /**
   * get a navpoint by place in playorder
   * @param  int $playOrder playorder
   * @return TocItem        navpoint
   */
  public function getByPlayOrder($playOrder) {
    foreach ($this->navpoints as $navpoint) {
      if ($navpoint->playOrder == $playOrder) {
        return $navpoint;
      }
    }
    return false;
  }

  /**
   * get a navpoint by idref
   * @param  int $id playorder
   * @return TocItem navpoint
   */
  public function getById($id) {
    foreach ($this->navpoints as $navpoint) {
      if ($navpoint->id == $id) {
        return $navpoint;
      }
    }
    return false;
  }
}
