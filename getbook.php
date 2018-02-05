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
  $curlstr = __DIR__."/lib/ficdl $url epub";
  echo "Downloading ".$_GET['title']."\n";
  echo "<pre>";
  system("$curlstr  2>&1");
  echo "</pre>";
  echo '<p><a href="http://th-netzhaut.local:8080/">booklist</a></p>';
}

function mcdl($url) {
  echo "$url";
  include_once(__DIR__."/sites/mcstories.php");
  $d = new McStories();
  $d->retrieveBook($url);
  echo '<p><a href="http://th-netzhaut.local:8080/">booklist</a></p>';
}

function makecover() {}