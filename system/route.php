<?php
// print __FILE__;


// require_login();

if (isset($_SERVER['REQUEST_URI'])) {
    $t_url = $_SERVER['REQUEST_URI'];
    // if (str_starts_with($url,'/')) $url = substr($url,1);
} else {
    throw new Exception('SYSTEM ERROR. Routing not possible.');
}
$url = explode("/",$t_url);
$page = ($url[1]) ?? NULL;
// Get allowed pages
$pages = scan(ROOTPATH . "pages/");
foreach ($pages as $key => $value) {
  $pages[$key] = pathinfo($value, PATHINFO_FILENAME);
}

// SAFECHECK
if (empty($page) || !in_array($page,$pages)) {
  $page = 'dashboard';
}

if(!require_login2()) {
  // Check if reigster or login!!
  if ($page == "register") {
    include_once ROOTPATH . 'pages/register.php';
  } else {
    include_once ROOTPATH . 'pages/login.php';
  }
  exit;
}

// pre($pages);
include_once ROOTPATH ."pages/{$page}.php";
