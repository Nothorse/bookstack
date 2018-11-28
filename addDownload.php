<?php
require_once(__DIR__ . "/config.php");
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
$file = '/tmp/ebooklib.log';
$log = `tail -n 10 $file`;

if (isset($_POST['url'])) {
  $url = $_POST['url'];
  echo (!isset($_POST['nohtml'])) ? "Accepted URL " . $url . " for download.<hr />" :'';
  error_log("Download queued for URL $url\n", 3, $file);
  file_put_contents('/tmp/download_queued', $url);
  echo (!isset($_POST['nohtml'])) ? "Current log:<br>" :'';
  echo (!isset($_POST['nohtml'])) ? nl2br($log) :'';
  echo (isset($_POST['nohtml'])) ? 'success' : '';
} else {
  echo (!isset($_POST['nohtml'])) ? "Current Log: <br/>" :'';
  echo (!isset($_POST['nohtml'])) ? nl2br($log) :'';
}