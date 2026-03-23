<?php
$host = '********';
$db   = '********';
$user = '********';
$pass = '********';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("DB-fel: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
