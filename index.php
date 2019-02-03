<?php
require "vendor/autoload.php";
//deactivate errordisplay
//error_reporting(0);
define('SHELL', false);
$index = new EBookLib\Dispatcher();
$path = (isset($_SERVER['PATH_INFO'])) ? explode('/', trim($_SERVER['PATH_INFO'], '/')) : array('');
$time = microtime(true);
$index->handleRequest($path, $time);
//print_r($debug);
