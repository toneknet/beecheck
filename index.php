<?php
// die('smurfs was here...');
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
define('ROOTPATH', dirname(__FILE__) . "/");
define('WWWPATH', "https://bikoll.tonek.se/");

require './system/functions.php';
require './system/config.php';
require './system/auth.php';

require './system/route.php';
// print "HEY";
// pre($_SERVER['HTTP_HOST']);
// pre($_SERVER['REQUEST_URI']);
// pre($_SERVER['SERVER_NAME']);


