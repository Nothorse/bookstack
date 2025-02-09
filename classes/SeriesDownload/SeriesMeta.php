<?php
namespace Ebooklib\SeriesDownload;

class SeriesMeta {

  /**
   * Url for the seies
   * @var string
   */
  private $seriesUrl;

  /**
   * array for stories
   * @var string[]
   */
  private $storyUrls = [];

  public function __construct($series) {
    exec(FFF . ' -n ' . $url, $urls, $result);
    if ($result == 0) {
      $this->storyUrls = $urls;
    }
  }
}
