<?php
namespace EBookLib;
/**
 * MetaBook
 **/
class MetaBook {

  /**
   * Author
   * @var string
   */
  public $author;

  /**
   * Author for sorting
   * @var string
   */
  public $sortauthor;

  /**
   * Title
   * @var string
   */
  public $title;

  /**
   * Summary
   * @var string
   */
  public $summary;

  /**
   * md5Id uniqueid
   * @var string
   */
  public $id;

  /**
   * Path to book
   * @var string
   */
  public $path;

  /**
   * Path of file without basedir
   * @var string
   */
  public $file;

  /**
   * Tags (dc:subject)
   * @var array
   */
  public $tags;

  /**
   * Update date in db
   * @var \DateTime
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
