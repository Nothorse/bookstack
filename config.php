<?php
define('USER', 'thomas');
define('BASEDIR', 'Books');
define('BASEURL', '/index.php');
if(substr($_SERVER['HTTP_HOST'], strlen($_SERVER['SERVER_PORT'])*-1) == $_SERVER['SERVER_PORT']) {
  define('SERVER', $_SERVER['HTTP_HOST']);
} else {
  define('SERVER', $_SERVER['HTTP_HOST'].':'.$_SERVER["SERVER_PORT"]);
}
#define('SERVER', $_SERVER['HTTP_HOST'].':'.$_SERVER["SERVER_PORT"]);
define('PORT', $_SERVER["SERVER_PORT"]);
