<?php
if ($_GET['url']) {
  $url = $_GET['url'];
  ficdl($url);
}

function ficdl($url) {
  $urlparts = explode('-', $_POST['url']);
  $u = $urlparts[1];
  $curlstr = "/Users/thomas/bin/ficdl $url epub";
  echo "Getting The book\n";
  echo "<pre>";
  echo system("$curlstr");
  echo "</pre>";
  echo '<p><a href="http://thbuch.local:8080/">booklist</a></p>';
}
