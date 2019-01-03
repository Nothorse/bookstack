<?php
namespace EBookLib\Epub;

/**
 * Dublin core metadata item.
 */
class DublinCoreItem {

  /**
   * Tag
   * @var string
   */
  private $tag;

  /**
  * Content
  * @var string
  */
  private $content;

  /**
   * OPF attribute name [optional]
   * @var string
   */
  private $opfAttribute;

  /**
   * OPF attribute content
   * @var string
   */
  private $opfContent;

  /**
   * Constructor
   * @param string $tag          tag
   * @param string $content      content
   * @param string $opfAttribute opf attribute
   * @param string $opfContent   opf content
   */
  public function __construct($tag, $content, $opfAttribute = null, $opfContent = null) {
    $this->tag = $tag;
    $this->content = $content;
    if ($opfAttribute) $this->opfAttribute = $opfAttribute;
    if ($opfContent) $this->opfContent = $opfContent;
  }

  public function setOpf($attribute, $content) {
    $this->opfAttribute = $attribute;
    $this->opfContent = $content;
  }

  /**
   * write to DOMDocument
   * @param  \DOMDocument $meta meta documnt
   * @return \DOMElement        itemref element
   */
  public function write($meta) {
    $element = $meta->createElement($this->tag, $this->content);
    if ($this->opfAttribute) {
      $element->setAttribute('opf:' . $this->opfAttribute, $this->opfContent);
    }
    return $element;
  }

  /**
   * return new DublinCoreItem from domelement
   * @param  \DOMElement $element dome element
   * @return DublinCoreItem            item
   */
  public static function parseElement($element) {
    $dcItem = new DublinCoreItem(
      $element->nodeName,
      $element->nodeValue
    );
    if ($element->hasAttributes()) {
      $attribute = $element->attributes->item(0);
      $dcItem->setOpf($attribute->name, $attribute->value);
    }
    return $dcItem;
  }

  /**
   * is author
   * @return bool [description]
   */
  public function isAuthor() {
    return $this->tag == 'dc:creator' && $this->opfContent == 'aut';
  }

  /**
   * is title
   * @return bool [description]
   */
  public function isTitle() {
    return $this->tag == 'dc:title';
  }

  /**
   * is subject
   * @return bool [description]
   */
  public function isSubject() {
    return $this->tag == 'dc:subject';
  }

  /**
   * getter
   * @return string content
   */
  public function getContent() {
    return $this->content;
  }

  /**
   * setter
   * @param string $content content
   */
  public function setContent($content) {
    $this->content = $content;
  }
}
