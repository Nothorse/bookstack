<?php
require_once('ebook.cls.php');
  if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false) {
    $protocol = "epub";
  } else {
    $protocol = "http";
  }
  
  if (substr($_SERVER['PATH_INFO'], 1, 3) == "get") {
    list($discard,$method, $file, $title) = explode('/', $_SERVER['PATH_INFO']);
    header("Content-Type: application/epub");
    echo file_get_contents($file);
    exit;
  }

  if (substr($_SERVER['PATH_INFO'], 1, 4) == "meta") {
    list($discard,$method, $file, $title) = explode('/', $_SERVER['PATH_INFO']);
    $book = new ebook($file);
    $zip = new ZipArchive();
    if ($zip->open($file)===TRUE){
      $container = simplexml_load_string($zip->getFromName(ebook::CONTAINER));
      $rootfile = $book->get_metafile($zip);
    }
    header("Content-Type: text/xml");
    echo $zip->getFromName($rootfile);
    exit;
  }


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name='viewport' content='width=320,user-scalable=false' />	<title>TH's Library</title>
</head>
<body style='padding:0;margin:0;'>
<p style='margin:0;border-bottom: 2px #333 solid;padding:0;width:100%'><a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=name" style='color: #333;text-decoration:none; display:block; border-right: 2px #333 solid; font-size: 16pt;padding:4px;margin:0;float:left;text-align:center;'>by Name</a><a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=date" style='color: #333;text-decoration:none; display:block; border-right: 2px #333 solid;font-size: 16pt;padding:4px;margin:0;float:left;text-align:center'>by Date</a><a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=author" style='color: #333;text-decoration:none; display:block; border-right: 2px #333 solid;font-size: 16pt;padding:4px;margin:0;float:left;text-align:center'>by Author</a><br style="clear:both"></p>

<?php 
#phpinfo();
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
    
    switch ($_GET['sort']) {
      case 'name':
        $list = listdir_by_name($path);
        break;
      case 'author':
        $list = listdir_by_author($path);
        break;
      case 'date':
        $list = listdir_by_date($path);
        break;
      default:
        $list = listdir_by_date($path);
    }
//     $list = ($_GET['sort'] == 'name') ? listdir_by_name($path) : listdir_by_date($path);
    
    foreach ($list as $file) { 
      list($name, $suffix) = explode('.', $file);
      if($suffix == 'epub') {
        $book = new ebook($file);
        #echo $book->title;
        #print_r($book);
        $name = preg_replace("/(?<=[^A-Z])([A-Z])/", " $1", $name);
        echo "<p style='padding:0;margin:0'><a href=\"".$protocol."://".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT'].$_SERVER['PHP_SELF']."/get/$file/".$book->title."\" style='color: #333;text-decoration:none; display:block; width: 100%; border-bottom: 2px #333 solid; font-size: 14pt;font-weight: bold;padding:4px;margin:0'>".$book->title." <span style=\"font-size:11pt; font-weight:normal;\"><br />by ".$book->author."</a></p>"; 
      }
    } 

    // Close 


function getSuffix($file) {
  list($name, $suffix) = explode('.', $file);
  return $suffix;
}

function listdir_by_date($path){
    $dir = opendir($path);
    $list = array();
    while($file = readdir($dir)){
        if ($file != '.' and $file != '..'){
            // add the filename, to be sure not to
            // overwrite a array key
            $ctime = filemtime($data_path . $file) . ',' . $file;
            $list[$ctime] = $file;
        }
    }
    closedir($dir);
    krsort($list);
    return $list;
}
function listdir_by_author($path){
    $dir = opendir($path);
    $list = array();
    while($file = readdir($dir)){
        if ($file != '.' and $file != '..'){
            // add the filename, to be sure not to
            // overwrite a array key
          list($name, $suffix) = explode('.', $file);
          if($suffix == 'epub') {
            $book = new ebook($file);
            $list[$book->sortauthor.$book->title] = $file;
          }
        }
    }
    closedir($dir);
    ksort($list);
    return $list;
}
function listdir_by_name($path){
    $dir = opendir($path);
    $list = array();
    while($file = readdir($dir)){
        if ($file != '.' and $file != '..'){
            // add the filename, to be sure not to
            // overwrite a array key
            $ctime = filectime($data_path . $file) . ',' . $file;
            $list[strtolower($file)] = $file;
        }
    } 
    closedir($dir);
    ksort($list);
    return $list;
}
?>
</body>
</html>
