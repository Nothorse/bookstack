<?php
if (!file_exists('.handoffsetup') || !file_exists('.handoff.url')) {
  setup();
  exit;
}
if (isset($_GET['setup'])) {
  setup();
  exit;
}
if ($_GET['url']) {
  $url = $_GET['url'];
  ficdl($url);
} else {
  header("Content-Type: text/html");
  $url = file_get_contents(".handoff.url");
  header("Location: $url");
  print <<<EOT
  <html>
  <head>
  <title>Link handoff</title>
  <body>
  <h1><a href="$url">$url</a></h1>
  </body>
EOT;

}

function ficdl($url) {
  print <<<EOT
  <!DOCTYPE html>
  <html lang="de">
  <head>
    <meta charset="utf-8" />
    <title>Saving Handoff...</title>
  </head>
  <body>
      <script type="text/javascript" language="javascript" charset="utf-8">
  <!--
  window.close();
  //-->
  </script>
  
  </body>
  </html>
EOT;
  file_put_contents(".handoff.url", $url);
}

function setup() {
  $port = $_SERVER["SERVER_PORT"];
  $http = $_SERVER["HTTP_SCHEME"];
  $host = $_SERVER["HTTP_HOST"];
  $scriptname = $_SERVER["SCRIPT_NAME"];
  print <<<EOT
  <!DOCTYPE html>
  <html lang="de">
  <head>
    <meta charset="utf-8" />
    <title>Setting up Handoff...</title>
  </head>
  <body>
  <p>Drag this into your bookmarks bar to save a page: <a href="javascript:var%20url;url=document.location;get='$http://$host:$port/$scriptname?url='+url;window.open(get,%22ficinput%22,'height=200,width=600');">&#10162;</a></p>
  <p>Save this to recall the saved page: <a href="$http://$host:$port/$scriptname">Saved Page</a></p>
  <p>To get going with handoff, call it first with this page, so you can access the setup page on your PDA or smartphone. I recommend mailing this URL to yourself. This link will set up a mail for you: Mail this <a href="mailto:replace@your.mail?subject=handoff&body=$http://$host:$port/$scriptname?setup=true">$http://$host:$port/$scriptname?setup=true</a>.</p>
  <p>Now save your first page and have fun.</p>
  </body>
  </html>
EOT;
file_put_contents(".handoffsetup", "$http://$host:$port/$scriptname?setup=true");
}
