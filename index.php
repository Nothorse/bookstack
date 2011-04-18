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
    list($discard,$method, $author) = explode('/', $_SERVER['PATH_INFO']);
    $list = $db->getBookList('added desc', 'where author = \'' . $author . '\'');
    printHeader();
    printBookList($list);
    exit;
  }

  if($path[0] == 'meta') {
    $book = $db->getBook($path[1]);
    $newbook = new ebook($book->file);
    header("Content-Type: text/plain");
    print_r($newbook);
    exit;
  }
  
  if($path[0] == 'edit') {
  }
  
  if($path[0] == 'read') {
    $book = $db->getBook($path[1]);
    $realbook = new ebook($book->file);
    header("Content-type: text/html");
    echo $realbook->getChapter($path[2]);
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
        printBookList($list);
        break;
      case 'author':
        $list = listdir_by_author($path, $db);
        printAuthorList($list);
        break;
      case 'date':
        $list = listdir_by_date($path, $db);
        printBookList($list);
        break;
      default:
        $list = listdir_by_date($path, $db);
        printBookList($list);
    }


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

function printBookList($list) {
  foreach($list as $book) {
    if(strlen($book->title) > 0) {
      echo "<p style='padding:0;margin:0'><a href=\"".getproto()."://".SERVER.BASEURL."/get/".$book->id."/".$book->title."\" style='color: #333;text-decoration:none; display:block; width: 100%; border-bottom: 2px #333 solid; font-size: 14pt;font-weight: bold;padding:4px;margin:0'>".$book->title." <span style=\"font-size:11pt; font-weight:normal;\"><br />by ".$book->author."</a></p>";
    }
  }
}

function printAuthorList($list) {
  foreach($list as $id => $author) {
      echo "<p style='padding:0;margin:0'><a href=\"http://".SERVER.BASEURL."/author/".$author[name]."/\" style='color: #333;text-decoration:none; display:block; width: 100%; border-bottom: 2px #333 solid; font-size: 14pt;font-weight: bold;padding:4px;margin:0'>".$id." <span style=\"font-size:11pt; font-weight:normal;\"><br />Books: ".implode(', ', $author['books'])."</a></p>";
  }
}

function printHeader() {
$self = 'http://'.SERVER.BASEURL;
$head = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name='viewport' content='width=320,user-scalable=false' />	<title>TH's Library Devel</title>
</head>
<body style='padding:0;margin:0;'>
<p style='margin:0;border-bottom: 2px #333 solid;padding:0;width:100%'><a href="$self?sort=name" style='color: #333;text-decoration:none; display:block; border-right: 2px #333 solid; font-size: 16pt;padding:4px;margin:0;float:left;text-align:center;'>by Name</a><a href="$self?sort=date" style='color: #333;text-decoration:none; display:block; border-right: 2px #333 solid;font-size: 16pt;padding:4px;margin:0;float:left;text-align:center'>by Date</a><a href="$self?sort=author" style='color: #333;text-decoration:none; display:block; border-right: 2px #333 solid;font-size: 16pt;padding:4px;margin:0;float:left;text-align:center'>by Author</a><br style="clear:both"></p>
EOT;
echo $head;
}

function getproto() {
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false) {
    return "epub";
  } else {
    return "http";
  }

}
?>
</body>
</html>
