<?php
namespace EBookLib;
/**
 * MetaBook
 **/
class MetaBook {
  /**
   * @var string
   */
  public $author;
  /**
   * @var string
   */
  public $sortauthor;
  /**
   * @var string
   */
  public $title;
  /**
   * @var string
   */
  public $summary;
  /**
   * @var string
   */
  public $id;
  /**
   * @var string
   */
  public $path;
  /**
   * @var string
   */
  public $file;
  /**
   * @var array
   */
  public $tags;
  /**
   * @var DateTime
   */
  public $updated;

  /**
   * Create md5 id.
   */
  public function create_id()
  {
    $this->id = md5('thcatgen' . $this->title . $this->author);
  }

  /**
   * @return string
   */
  public function taglist()
  {
    if (count($this->tags) > 0) {
      return implode(', ', $this->tags);
    }
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return basename($this->file);
  }

  /**
   * Truncate summary.
   * @param  int $char number of characters
   * @return string
   */
  public function trunc_summary($char) {
    $ct = strlen($this->summary);
    if ($ct>$char) {
      return substr($this->summary, 0, 100) . "...";
    }
    return $this->summary;
  }

  /**
   * Get the full path.
   * @return string
   */
  public function getFullFilePath() {
    return BASEDIR . '/' . $this->file;
  }

}
