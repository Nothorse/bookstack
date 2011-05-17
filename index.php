<?php
/** 
 * Change the path to your folder. 
 * 
 * This must be the full path from the root of your 
 * web space. If you're not sure what it is, ask your host. 
 * 
 * Name this file index.php and place in the directory. 
 */ 

// Define the full path to your folder from root 
$path = "/Users/thomas/public/ffic/"; 
require_once('config.php');
require_once('ebook.cls.php');
require_once('stanza.cls.php');
require_once('browser.cls.php');
require_once('library.cls.php');
$db = new library();
$ua = $_SERVER['HTTP_USER_AGENT'];
header("X-UA-REQUEST: $ua");
if (strpos($ua, 'Stanza') === false) {
  $display = new browserdisplay();
} else {
  $display = new stanzadisplay();
}

header("X-Display: ". get_class($display));
header("X-SELFZURL: ".SERVER.BASEURL);

  
 $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));
 
 if ($path[0] == "get") {
    $book = $db->getBook($path[1]);
    #print_r($book);
    header("Content-Type: application/epub");
    echo file_get_contents($book->file);
    exit;
  }

  if ($path[0] == "author") {
    setcookie('booksel', 'author', 0, '/');
    list($discard,$method, $author) = explode('/', $_SERVER['PATH_INFO']);
    setcookie('selval', $author, 0, '/');
    $list = $db->getBookList('added desc', 'where author = \'' . sqlite_escape_string($path[1]) . '\'');
    printHeader();
    $alist = listdir_by_author($path, $db);
    printAuthorList($alist, 'author', $author);
    printBookList($list, 'books');
    exit;
  }
  
  if ($path[0] == 'tag') {
    setcookie('booksel', 'tag', 0, '/');
    setcookie('selval', $path[1], 0, '/');
    $list = $db->getTaggedBooks($path[1]);
    printHeader();
    $alist = $db->getTagList(false);
    printAuthorList($alist, 'tag', $path[1]);
    printBookList($list, 'books');
    exit;
  }

  if($path[0] == 'meta') {
    $book = $db->getBook($path[1]);
    $newbook = new ebook($book->file);
    header("Content-Type: text/plain");
    #print_r($newbook);
    echo $newbook->allmeta->saveXML();
    exit;
  }
  
  if($path[0] == 'show') {
    $book = $db->getBook($path[1]);
    if(getproto() == 'epub') {
      header("Content-Type: application/epub");
      echo file_get_contents($book->file);
      exit;
    }
    printHeader();
    $type = (isset($_COOKIE['booksel']))? $_COOKIE['booksel'] : 'author';
    $current = (isset($_COOKIE['selval']))? $_COOKIE['selval'] : $book->author;
    $list = ($type == 'tag') ? $db->getTagList() : $db->getAuthorlist();
    printAuthorList($list, $type, $current);
    $booklist = ($type == 'tag')? $db->getTaggedBooks($current) : $db->getBookList('added desc', 'where author = \'' . sqlite_escape_string($current) . '\'');
    printBookList($booklist, 'books', $path[1]);
    echo showDetails(new ebook($book->file));
    setcookie('booksel', '');
    setcookie('selval', '');
    exit;
  }
  
  if($path[0] == 'edit') {
    $book = $db->getBook($path[1]);
    $realbook = new ebook($book->file);
    $realbook->id = $path[1];
    $url = $_SERVER['PHP_SELF'];
    $realbook->title = (isset($_POST['title'])) ? $_POST['title']:$realbook->title;
    $realbook->author = (isset($_POST['author'])) ? $_POST['author']:$realbook->author;
    $realbook->sortauthor = (isset($_POST['author'])) ? strtolower($_POST['author']):$realbook->sortauthor;
    if (isset($_POST['tags'])) {
      $tags = explode(',', $_POST['tags']);
      $realbook->tags = array();
      foreach($tags as $tag) {
        $realbook->tags[] = trim($tag);
      }
    }
    $realbook->summary = (isset($_POST['summary'])) ? $_POST['summary']:$realbook->summary;
    $realbook->modify_meta();
    $db->updateBook($realbook);
    printHeader();
    printHeader();
    $type = $_COOKIE['booksel'];
    $current = $_COOKIE['selval'];
    $list = ($type == 'tag') ? $db->getTagList() : $db->getAuthorlist();
    printAuthorList($list, $type, $current);
    $booklist = ($type == 'tag')? $db->getTaggedBooks($current) : $db->getBookList('added desc', 'where author = \'' . sqlite_escape_string($current) . '\'');
    printBookList($booklist, 'books', $path[1]);
    echo getEditForm($realbook, $url);
    setcookie('booksel', '');
    setcookie('selval', '');
    exit;
  }
  
  if($path[0] == 'read') {
    $book = $db->getBook($path[1]);
    $realbook = new ebook($book->file);
    header("Content-type: text/html");
    echo $realbook->getChapter($path[2]);
    exit;
  }
  
  if($path[0] == 'delete') {
    $book = $db->getBook($path[1]);
    $db->deleteBook($book);
    header("Location: http://".SERVER.BASEURL);
    exit;
  }

  $items = listdir_by_date($path, $db);
  foreach ($items as $id => $item) {
    $book = new ebook($path.$item);
    $authors[$book->sortauthor][$book->id] = $book;
    $allbooks[$book->id]         = $book;
  }


    printHeader();
    switch ($_GET['sort']) {
      case 'name':
        $list = listdir_by_name($path, $db);
        printBookList($list, 'bookswide');
        break;
      case 'author':
        $list = listdir_by_author($path, $db);
        printAuthorList($list, 'author');
        $books = $db->getBooklist('title asc');
        printBookList($books, 'books');
        break;
      case 'date':
        $list = listdir_by_date($path, $db);
        printBookList($list, 'bookswide');
        break;
      case 'tags':
        $list = $db->getTagList(false);
        printAuthorList($list, 'tag');
        break;
      default:
        $list = listdir_by_date($path, $db);
        printBookList($list);
    }
    setcookie('booksel', '');
    setcookie('selval', '');


function getSuffix($file) {
  list($name, $suffix) = explode('.', $file);
  return $suffix;
}

function listdir_by_date($path, $db){
  return $db->getBooklist();
}
function listdir_by_author($path, $db){
  return $db->getAuthorlist('sortauthor asc');
}
function listdir_by_name($path, $db){
  return $db->getBooklist('title asc');
}

function printBookList($list, $divid = 'list', $curid = null) {
  echo "<div id='$divid'><ul>";
  foreach($list as $book) {
    $current =($curid == $book->id) ? ' class="current"' : '';
    if(strlen($book->title) > 0) {
      echo "<li$current><a href=\"".getproto()."://".SERVER.BASEURL."/show/".$book->id."/".$book->title."\">".$book->title." <span class=\"byline\">".$book->author."</a></li>\n";
    }
  }
  echo "<ul></div>";
}

function printAuthorList($list, $what, $current= null) {
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

function getFormattedList($type = 'author') {
  $db = new library();
  $list = $db->getAuthorList();
  $formattedlist = "<ul>\n";
  foreach($list as $author => $rec) {
    $formattedlist .= "<li><a href=\"http://".SERVER.BASEURL."/$what/".$author['name']."/\">$author</a></li>\n";
  }
  $formattedlist .= '</ul>';
  return $formattedlist;
}

function listTags() {
  $db = new library();
  $list = $db->getTagList(false);
  $taglist = '';
  foreach($list as $id => $tag) {
    $taglist .= "<li><a href=\"\">".$tag['name']."</a></li>";
  }
  return "<ul>$taglist</ul>";
}

function printHeader() {
$self = 'http://'.SERVER.BASEURL;
$taglist = listTags();
$head = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name='viewport' content='width=320,user-scalable=false' />	<title>TH's Library Devel</title>
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

function buildPage() {


}

function getproto() {
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false) {
    return "epub";
  } else {
    return "http";
  }

}

function showDetails($book, $protocol = 'http') {
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

function getEditform($book, $url) {
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
?>
</body>
</html>
