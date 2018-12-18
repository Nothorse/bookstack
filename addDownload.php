<html><head>
  <title>DownloadQueue</title>
  <style>
    body {
      background:  #DADFE8;
    }
  </style>
</head>
<body>
<?php
require_once(__DIR__ . "/config.php");
require __DIR__ . "/vendor/autoload.php";
use EBookLib\Library as Library;
  /**
   * [printLog description]
   * @param [type] $lastlog [description]
   */
  function printLog($lastlog) {
    echo "<table>";
    foreach ($lastlog as $key => $value) {
      echo "<tr>";
      echo "<td>" . $value['datestamp'] . "</td><td>" . $value['entry'] . "</td>";
      echo "</tr>";
    }
    echo "</table>";
  }
/**
 * addDownload.php
 *
 * @package   EbookLib
 * @copyright intevo.websolutions gmbh 2018
 * @author    TH <thomas.hassan@teambox.at>
 */
if (!isset($_POST['nohtml'])) {
  echo <<<EOF
<body><form method="post">
<input type="text" name="url" style="width: 90%; margin: 0 auto;"><br>
<input type="submit" >
</form>
EOF;
  echo "<hr />";
}
$file = __DIR__ . '/tmp/ebooklib.log';
$log = `tail -n 10 $file`;

$library = new Library();
if (isset($_POST['url'])) {
  $url = $_POST['url'];
  echo (!isset($_POST['nohtml'])) ? "Accepted URL " . $url . " for download.<hr />" :'';
  $library->logThis("Download queued for URL $url\n");
  $library->queueThis($url);
  $lastlog = $library->getLastLog();
  echo (!isset($_POST['nohtml'])) ? "Current log:<br>" :'';
  printLog($lastlog);
  echo (isset($_POST['nohtml'])) ? 'success' : '';
} else {
  $lastlog = $library->getLastLog();
  echo (!isset($_POST['nohtml'])) ? "Current Log: <br/>" :'';
  printLog($lastlog);

}
?>
</body></html>
