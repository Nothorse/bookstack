<?php
namespace EBookLib\Epub;

/**
 * Metadata for epub.
 */
class Metadata {

  /**
   * namespaces
   * @var array
   */
  private $namespaces = ['xmlns:dc' => 'http://purl.org/dc/elements/1.1/',
                         'xmlns:opf' => 'http://www.idpf.org/2007/opf'];

  /**
   * dublin core items
   * @var DublinCoreItem[];
   */
  private $dcItems = [];

  /**
   * meta items
   * @var MetaItem[];
   */
  private $metaItems = [];
  /**
   * constructor
   * @param \DOMDocument $dom dom
   */
  public function __construct($dom) {
    $metalist = $dom->getElementsByTagName('metadata')->item(0)->childNodes;
    for ($i = 0; $i < $metalist->length; $i++) {
      $element = $metalist->item($i);
      $tag = $element->nodeName;
      if (\strpos($tag, 'dc:') !== false) {
        $this->dcItems[] = DublinCoreItem::parseElement($element);
      }
      if ($tag == 'meta') {
        $this->metaItems[$element->getAttribute('name')] = MetaItem::parseElement($element);
      }
    }
  }

  /**
   * Write Metadata to xml
   * @param \DOMElement  $root root element
   * @param \DOMDocument $dom  DomDocument
   */
  public function writeElement($root, $dom) {
    $metadataelement = $root->getElementsByTagName('metadata');
    if ($metadataelement->length > 0) {
      $root->removeChild($metadataelement->item(0));
    }
    $metadata = $dom->createElement('metadata');
    foreach ($this->namespaces as $prefix => $ns) {
      $metadata->setAttribute($prefix, $ns);
    }
    foreach ($this->dcItems as $item) {
      $metadata->appendChild($item->write($dom));
    }
    foreach ($this->metaItems as $item) {
      $metadata->appendChild($item->write($dom));
    }
    $metadata->normalize();
    $root->appendChild($metadata);
  }

  /**
   * setter for author tag
   * @param string[] $authors Authors
   */
  public function setAuthors($authors) {
    foreach ($this->dcItems as $key => $item) {
      if ($item->isAuthor()) unset($this->dcItems[$key]);
    }
    foreach ($authors as $key => $author) {
      $author = new DublinCoreItem(
        'dc:creator', $author, 'role', 'aut'
      );
      $this->dcItems[] = $author;
    }
  }

  /**
   * getter for author tag
   * @return string[] Authors
   */
  public function getAuthors() {
    $authors = [];
    foreach ($this->dcItems as $key => $item) {
      if ($item->isAuthor())$authors[] = $item->getContent();
    }
    return $authors;
  }

  /**
   * setter for subject tag
   * @param string[] $subjects subjects
   */
  public function setSubjects($subjects) {
    foreach ($this->dcItems as $key => $item) {
      if ($item->isSubject()) unset($this->dcItems[$key]);
    }
    foreach ($subjects as $key => $subject) {
      $subject = new DublinCoreItem(
        'dc:subject', $subject
      );
      $this->dcItems[] = $subject;
    }
  }

  /**
   * getter for subject tag
   * @return string[] subjects
   */
  public function getSubjects() {
    $subjects = [];
    foreach ($this->dcItems as $key => $item) {
      if ($item->isSubject()) $subjects[] = $item->getContent();
    }
    return $subjects;
  }

  /**
   * setter for title tag
   * @param string $title Authors
   */
  public function setTitle($title) {
    foreach ($this->dcItems as $key => $item) {
      if ($item->isTitle()) $item->setContent($title);
    }
  }

  /**
   * getter for title tag
   * @return string title
   */
  public function getTitle() {
    $title = [];
    foreach ($this->dcItems as $key => $item) {
      if ($item->isTitle()) return $item->getContent();
    }
    return 'Untitled';
  }

  /**
   * getter
   * @return string cover id in manifest
   */
  public function getCover() {
    foreach ($this->metaItems as $key => $item) {
      if ($item->isCover()) return $item->getContent();
    }
    return '';
  }

  /**
   * setter for cover tag
   * @param string $coverId Cover id
   */
  public function setCover($coverId) {
    $exists = false;
    foreach ($this->metaItems as $key => $item) {
      if ($item->isCover()) {
        $item->content = $coverId;
        $this->metaItems[$key] = $item;
        $exists = true;
      }
    }
    if (!$exists) {
      $this->metaItems[] = new MetaItem('cover', $coverId);
    }
  }


}
