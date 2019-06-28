<?php
namespace EBookLib;

/**
 * Basic browser output for lists.
 */
class BrowserDisplay {

  /**
   * Generate booklist and echo out.
   * @method printBookList
   * @param  array  $list  array of books
   * @param  string $divid idv id(?)
   * @param  string $curid current id
   * @return void   no return
   */
  public function printBookList($list, $divid = 'list', $curid = null) {
    $time = microtime(true);
    echo "<div id='$divid'><ul id='booklist'>";
    $template = new Template('booklistentry');
    foreach($list as $book) {
      $current =($curid == $book->id) ? ' class="current"' : '';
      if(strlen($book->title) > 0) {
        $data = array(
          'current' => $current,
          'title' => $book->title,
          'delete' => "http://".SERVER.BASEURL."/delete/".$book->id.'/'.$book->title,
          'author' => $book->author,
          'tags' => $book->taglist(),
          'summary' => $book->trunc_summary(250),
          'finder' => "ebooklib://at.grendel.ebooklib?" . $book->getFullFilePath(),
          'show' => $this->getproto()."://".SERVER.BASEURL."/show/".$book->id."/".$book->title,
          'download' => $this->getproto()."://".SERVER.BASEURL."/get/".$book->id.'/'. $book->file,
        );
        $data['complete'] = (strpos($data['tags'], 'In-Progress') === false) ?
          ' complete' : '';
        echo $template->render($data);
      }
    }
    global $debug;
    $debug['Rendertime'] = microtime(true) - $time;

    echo "<ul></div>";
  }

  /**
   * [listTags description]
   * @method listTags
   * @return [type]   [description]
   */
  public function listTags() {
    $db = new Library();
    $list = $db->getTagList(false);
    $taglist = '';
    foreach($list as $id => $tag) {
      $taglist .= "<li><a href=\"\">".$tag['name']. ' (' . $tag['books'][0] . " books)</a></li>";
    }
    return "<ul>$taglist</ul>";
  }

  /**
   * print header
   * @method printHeader
   * @return string      html code
   */
  public function printHeader() {
    $time = microtime(true);
    $data = array();
    $data['self'] = 'http://'.SERVER.BASEURL;
    //$data['taglist'] = $this->listTags();
    $head = new Template('header');
    echo $head->render($data);
    global $debug;
    $debug['Head render'] = microtime(true) - $time;
  }

  /**
   * print footer of page
   * @return string html code
   */
  public function printFooter($time) {
    $tpl = new Template('footer');
    $fulltime = round((microtime(true) - $time) * 1000, 3);
    echo $tpl->render(['foot' => true, 'time' => $fulltime]);
  }


  /**
   * ignoreed function
   * @method buildPage
   * @deprecated ignore it
   * @return string    html code
   */
  public function buildPage() {


  }

  /**
   * get request protocol.
   * @deprecated irrelevant don't use anymore
   * @return string
   */
  public function getproto() {
    return "http";
  }

  /**
   * Create and show detail screen.
   * @method string showDetails(Ebook, string)
   * @param  Ebook  $book     the book to show details for
   * @param  string $protocol http (only used to have odps)
   * @return string
   */
  public function showDetails($book, $protocol = 'http') {
    $data = array();
    $data['geturl'] = "$protocol://".SERVER.BASEURL."/get/".$book->id.'/'.$book->file;
    $data['editurl'] = "http://".SERVER.BASEURL."/edit/".$book->id.'/'.$book->title;
    $data['deleteurl'] = "http://".SERVER.BASEURL."/delete/".$book->id.'/'.$book->title;
    $data['refreshurl'] = "http://".SERVER.BASEURL."/refresh/".$book->id.'/'.$book->title;
    $data['authorurl'] = "http://".SERVER.BASEURL."/author/".$book->author;
    $seriestpl = new Template('seriesdisplay');
    $data['seriesurl'] = "http://".SERVER.BASEURL."/series/".$book->seriesId;
    $data['seriesname'] = $book->getSeriesName();
    $data['seriesvol'] = $book->getSeriesVolume();
    $data['series'] = ($book->getSeriesName()) ? $seriestpl->render($data) : '';
    $data['toc'] = $book->getFormattedToc("http://".SERVER.BASEURL);
    $data['finder'] = "ebooklib://at.grendel.ebooklib?" . $book->getFullFilePath();
    $data['title'] = $book->title;
    $data['summary'] = $book->summary;
    $data['tags'] = $book->taglist();
    $data['author'] = $book->author;
    $data['cover'] = $book->getCover(true);
    $data['complete'] = (strpos($data['tags'], 'In-Progress') === false) ?
      ' complete' : '';
    $details = new Template('bookdetails');
    return $details->render($data);
  }

  /**
   * @param Ebook  $book
   * @param string $url
   * @return string
   */
  public function getEditform($book, $url) {
    $tags = implode(', ', $book->tags);
    $backurl = str_replace('edit', 'show', $url);
    $form = new Template('editform');
    $data['title'] = $book->title;
    $data['summary'] = $book->summary;
    $data['tags'] = $book->taglist();
    $data['author'] = $book->author;
    $data['seriesname'] = $book->getSeriesName();
    $data['seriesvol'] = $book->getSeriesVolume();
    $data['backurl'] = "http://".SERVER.BASEURL."/show/".$book->id.'/'.$book->title;
    return $form->render($data);
  }

  /**
   * Print the log.
   * @param array $log log entries
   */
  public function printLog($log) {
    echo "<table>";
    foreach ($log as $key => $value) {
      echo "<tr>";
      echo "<td class='date'>" . $value['datestamp'];
      echo "</td><td class='booktitle'>" . $value['entry'] . "</td>";
      echo "</tr>";
    }
    echo "</table>";
  }

  private function getSeriesMenu($book) {
    $db = new Library();
    $serieslist = $db->getSeriesList();
    foreach ($serieslist as $id => $series) {

    }
  }

}
