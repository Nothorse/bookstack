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
   * OPF attributes [optional]
   * @var array
   */
  private $opfAttributes = [];

  /**
   * Constructor
   * @param string $tag          tag
   * @param string $content      content
   * @param array  $opfAttributes opf attribute
   */
  public function __construct($tag, $content, $opfAttributes = null) {
    $this->tag = $tag;
    $this->content = $content;
    if ($opfAttributes) {
      $this->opfAttributes = $opfAttributes;
    }
  }

  /**
   * set OPF Attributes
   * @param string $attribute name
   * @param string $content   content
   */
  public function setOpf($attribute, $content) {
    $this->opfAttributes[$attribute] = $content;
  }

  /**
   * write to DOMDocument
   * @param  \DOMDocument $meta meta documnt
   * @return \DOMElement        itemref element
   */
  public function write($meta) {
    $element = $meta->createElement($this->tag, $this->content);
    if (!empty($this->opfAttributes)) {
      foreach ($this->opfAttributes as $attribute => $content) {
        $element->setAttribute($attribute, $content);
      }
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
      foreach ($element->attributes as $attribute) {
        $dcItem->setOpf($attribute->nodeName, $attribute->nodeValue);
      }
    }
    return $dcItem;
  }

  /**
   * is author
   * @return bool [description]
   */
  public function isAuthor() {
    return $this->tag == 'dc:creator' && $this->opfAttributes['opf:role'] == 'aut';
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
   * is summary (dc:description)
   * @return bool [description]
   */
  public function isSummary() {
    return $this->tag == 'dc:description';
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
