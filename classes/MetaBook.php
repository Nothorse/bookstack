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
   * Path to book files in zip
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
   * series information
   * @var array
   */
  public $series;

  /**
   * Series id
   * @var int;
   */
  public $seriesId;

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

  /**
   * getter for series name
   * @return string name
   */
  public function getSeriesName() {
    return isset($this->series[0]) ? $this->series[0] : '' ;
  }

  /**
   * get volume of series
   * @return int volume no.
   */
  public function getSeriesVolume() {
    return isset($this->series[1]) ? $this->series[1] : 0 ;
  }

}
