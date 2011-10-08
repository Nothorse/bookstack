<?php
//deactivate errordisplay
//error_reporting(0);
define('SHELL', false);
require_once('router.php');

$index = new Dispatcher(PORT == ODPS);
$path = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$index->handleRequest($path);