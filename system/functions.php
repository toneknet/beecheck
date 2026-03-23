<?php
function pre($str) {
  //   if (!isSuperUser()) return;
  print "<pre>";
  if (is_array($str)) {
    print_r($str);
  } else {
    print($str);
  }
  print "</pre>";
}
function scan($strPath) {
  return (array) array_diff(scandir($strPath), array('..', '.'));
}
function _header() {
  include_once ROOTPATH . 'system/_header.php';
}
function _footer() {
  include_once ROOTPATH . 'system/_footer.php';
}
function _menu() {
  include_once ROOTPATH . 'system/_menu.php';
}
function url($path = "") {
  if (!empty($path) && str_ends_with($path,"/")) $path.="/";
  return  WWWPATH.$path;
}

function returnBtn($path = '', $caption = "Tillbaka") {
  return "<a href='" . url($path) . "' class='returnBtn'>&#x21A9; {$caption}</a>";
}