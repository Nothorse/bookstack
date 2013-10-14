<?php


if ($_GET['url']) {
  $url = $_GET['url'];
  if($url == 'about:blank') {
    header('Location: http://th-netzhaut.local:8080/');
    exit;
  }
  makecover();
  if(strpos($url, 'mcstories') === false) {
    ficdl($url);
  } else {
    mcdl($url) ;
  }
}

function ficdl($url) {
  $urlparts = explode('-', $_POST['url']);
  $u = $urlparts[1];
  $curlstr = __DIR__."/lib/ficdl $url epub";
  echo "Downloading ".$_GET['title']."\n";
  echo "<pre>";
  echo system("$curlstr");
  echo "</pre>";
  echo '<p><a href="http://th-netzhaut.local:8080/">booklist</a></p>';
}

function mcdl($url) {
  //echo "$url";
  include_once(__DIR__."/sites/mcstories.php");
  $d = new Downloader();
  $d->retrieveBook($url);
  echo '<p><a href="http://th-netzhaut.local:8080/">booklist</a></p>';
}

function makecover() {}