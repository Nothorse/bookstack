<?php
class browserdisplay {
  public function printBookList($list, $divid = 'list', $curid = null) {
    echo "<div id='$divid'><ul>";
    foreach($list as $book) {
      $current =($curid == $book->id) ? ' class="current"' : '';
      if(strlen($book->title) > 0) {
        echo "<li$current><a href=\"".$this->getproto()."://".SERVER.BASEURL."/show/".$book->id."/".$book->title."\">".$book->title." <span class=\"byline\">".$book->author."</a></li>\n";
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
    $self = 'http://'.SERVER.BASEURL;
    $taglist = $this->listTags();
    $head = <<<EOT
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
      <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name='viewport' content='width=620,user-scalable=true' />	<title>TH's Library Devel</title>
    <script src="/jquery.js" type="text/javascript" language="javascript" charset="utf-8"></script>
    <link rel="stylesheet" href="/ui.css" type="text/css" media="all">
    </head>
    <body style='padding:0;margin:0;'>
    <div id="bluebar">
    <ul>
    <li class="category">
    <a href="$self?sort=name">Books</a>
    </li>
    <li class="category">
    <a href="$self?sort=author">Authors</a>
    </li>
    <li class="category">
    <a href="$self?sort=date">Recently Added</a>
    </li>
    <li class="category">
    <a href="$self?sort=tags">Tags</a>
    </li>
    <li class="category">
    <a href="$self?sort=list">Lists</a>
    </li>
    </ul>
    </div>
EOT;
    echo $head;
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
    $geturl = "$protocol://".SERVER.BASEURL."/get/".$book->id.'/'.$book->title;
    $editurl = "http://".SERVER.BASEURL."/edit/".$book->id.'/'.$book->title;
    $deleteurl = "http://".SERVER.BASEURL."/delete/".$book->id.'/'.$book->title;
    $toc = $book->getFormattedToc("http://".SERVER.BASEURL);
    $details = <<<EOT
    <div id="details">
      $toc
      <h1>$book->title</h1>
      <h2>$book->author</h2>
      <p>$book->summary</p>
      <p><a href="$geturl">Download</a> | <a href="$editurl">Edit Metadata</a> | <a href="$deleteurl">Delete Book</a></p>
    </div>
EOT;
    return $details;
  }
  
  public function getEditform($book, $url) {
    $tags = implode(', ', $book->tags);
    $form = <<<EOT
  <div id="edit">
  <style type="text/css" title="text/css">
  <!--
  #edit {
    border: 2px #000 solid;
    padding: 3px;
  }
  
  form {
    width: 800px;
    position: relative;
  }
  label {
    font-size: 16px;
    font-weight: bold;
    display: block;
    line-height: 25px;
    margin: 0 0 5px 0;
    width: 800px;
    position:relative;
  }
  
  input, textarea {
    width: 700px;
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
      <label>Summary: <textarea name="summary">$book->summary</textarea>
      <button type="submit" id="submit" value="Update Book">Update Book</button>
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