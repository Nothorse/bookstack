<?php
//deactivate errordisplay
//error_reporting(0);
define('SHELL', false);
require_once('dispatcher.php');

$index = new Dispatcher(PORT == ODPS);
$path = (isset($_SERVER['PATH_INFO'])) ? explode('/', trim($_SERVER['PATH_INFO'], '/')) : array('');
$index->handleRequest($path);