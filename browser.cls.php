<?php
class browserdisplay {
  public function printBookList($list, $divid = 'list', $curid = null) {
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
          'summary' => $book->trunc_summary(180),
          'finder' => "ebooklib://at.grendel.ebooklib?" . $book->file,
          'show' => $this->getproto()."://".SERVER.BASEURL."/show/".$book->id."/".$book->title,
          'download' => $this->getproto()."://".SERVER.BASEURL."/get/".$book->id.'/'.$book->title . '.epub',
        );
        echo $template->render($data);
      }
    }
    echo "<ul></div>";
  }

  public function printAuthorList($list, $what, $current= null) {
    echo "<div id='list'><ul>";
    foreach($list as $id => $author) {
      $class='';
      if($current == $id) {
        $class = " class='current'";
      }
      echo "<li$class><a href=\"http://".SERVER.BASEURL."/$what/".$author['name']."/\">".$id."</a></li>\n";
    }
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
    $data = array();
    $data['self'] = 'http://'.SERVER.BASEURL;
    $data[taglist] = $this->listTags();
    $head = new Template('header');
    echo $head->render($data);
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

  public function showDetails($book, $protocol = 'http') {
    $data = array();
    $data['geturl'] = "$protocol://".SERVER.BASEURL."/get/".$book->id.'/'.$book->title . '.epub';
    $data['editurl'] = "http://".SERVER.BASEURL."/edit/".$book->id.'/'.$book->title;
    $data['deleteurl'] = "http://".SERVER.BASEURL."/delete/".$book->id.'/'.$book->title;
    $data['toc'] = $book->getFormattedToc("http://".SERVER.BASEURL);
    $data['finder'] = "ebooklib://at.grendel.ebooklib?" . $book->file;
    $data['title'] = $book->title;
    $data['summary'] = $book->summary;
    $data['tags'] = $book->taglist();
    $data['author'] = $book->author;
    $details = new Template('bookdetails');
    return $details->render($data);
  }

  public function getEditform($book, $url) {
    $tags = implode(', ', $book->tags);
    $backurl = str_replace('edit', 'show', $url);
    $form = <<<EOT
  <div id="edit">
  <style type="text/css" title="text/css">
  <!--
  #edit {
    border: 2px #000 solid;
    padding: 3px;
    width: 95%;
    margin: 0 auto;
  }

  form {
    width: 90%;
    position: relative;
  }
  label {
    font-size: 16px;
    font-weight: bold;
    display: block;
    line-height: 25px;
    margin: 0 0 5px 0;
    width: 90%;
    position:relative;
  }

  input, textarea {
    width: 80%;
    height: 25px;
    font-size: 16px;
    border: none;
    left:20px;
    position: relative;
    display: block;
  }

  textarea {
    height: 150px;
    line-height: 25px;
  }
  -->
  </style>
    <form action="$url" method="post">
      <label>Title: <input type="text" name="title" value="$book->title"></label>
      <label>Author: <input type="text" name="author" value="$book->author"></label>
      <label>Tags: <textarea name="tags">$tags</textarea></label>
      <label>Summary: <textarea name="summary">$book->summary</textarea></label>
      <button type="submit" id="submit" value="Update Book">Update Book</button>
      <a href="$backurl">Cancel Edit</a>
    </form>
  </div>
EOT;
  return $form;
  }

  public function printFoot() {
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
   }
}