<?php
define('ROOTPATH', str_replace("/system","",dirname(__FILE__) . "/"));
define('WWWPATH', "https://********/"); // with trailing slash

$host = '********';
$db   = '********';
$user = '********';
$pass = '********';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("DB-fel: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4'); 
