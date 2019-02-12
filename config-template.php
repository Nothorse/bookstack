<?php
define('TRASH', '<path>');
define('BASEDIR', '<path>');
define('BASEURL', '/index.php');
define('HTML', 8080);
define('FFF', "/path/to/fanficfare");
define('COVERGEN', "/path/to/tenprintcover.py or equiv");
if(!SHELL) {
if(substr($_SERVER['HTTP_HOST'], strlen($_SERVER['SERVER_PORT'])*-1) == $_SERVER['SERVER_PORT']) {
  define('SERVER', $_SERVER['HTTP_HOST']);
} else {
  define('SERVER', $_SERVER['HTTP_HOST'].':'.$_SERVER["SERVER_PORT"]);
}
define('PORT', $_SERVER["SERVER_PORT"]);
}

date_default_timezone_set('Europe/Vienna');
