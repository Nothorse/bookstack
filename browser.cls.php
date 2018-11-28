<?php
class browserdisplay {
  public function printBookList($list, $divid = 'list', $curid = null) {
    $time = microtime(true);
    echo "<div id='$divid'><ul>";
    /** @var ebook $book */
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
          'download' => $this->getproto()."://".SERVER.BASEURL."/get/".$book->id.'/'.$book->title . '.epub',
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

  public function getFormattedList($type = 'author') {
    $db = new library();
    $list = $db->getAuthorList();
    $formattedlist = "<ul>\n";
    foreach($list as $author => $rec) {
      $formattedlist .= "<li><a href=\"http://".SERVER.BASEURL."/$what/".$author['name']."/\">$author</a></li>\n";
    }
    $formattedlist .= '</ul>';
    return $formattedlist;
  }

  public function listTags() {
    $db = new library();
    $list = $db->getTagList(false);
    $taglist = '';
    foreach($list as $id => $tag) {
      $taglist .= "<li><a href=\"\">".$tag['name']."</a></li>";
    }
    return "<ul>$taglist</ul>";
  }

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

  public function buildPage() {


  }

  public function getproto() {
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false) {
      return "epub";
    } else {
      return "http";
    }

  }

  /**
   * @param Ebook  $book
   * @param string $protocol
   * @return string
   */
  public function showDetails($book, $protocol = 'http') {
    $data = array();
    $data['geturl'] = "$protocol://".SERVER.BASEURL."/get/".$book->id.'/'.$book->title . '.epub';
    $data['editurl'] = "http://".SERVER.BASEURL."/edit/".$book->id.'/'.$book->title;
    $data['deleteurl'] = "http://".SERVER.BASEURL."/delete/".$book->id.'/'.$book->title;
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
    return $form->render($data);
  }

  public function printFoot() {
    $time = microtime(true);
    $foot = <<<EOT
  <script type="text/javascript" language="javascript" charset="utf-8">
  var pos = $('#list li.current').position().top;
  pos = pos - 100;
  $('#list').scrollTop(pos);
  var pos = $('#books li.current').position().top;
  //pos = pos - 100;
  $('#books').scrollTop(pos);
  </script>
  </body>
  </html>
EOT;
    echo $foot;
    global $debug;
    $debug['Rendertime'] = microtime(true) - $time;
    }

}