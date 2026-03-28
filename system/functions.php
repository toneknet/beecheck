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

function returnBtnHistory($caption = "Tillbaka") {
  return "<a href='javascript:history.back()' class='returnBtn'>&#x21A9; {$caption}</a>";
}

function noAccess() {
  _header();
  ?>
  <main class="app-main">
     <div class="card apiary-card">
        <h1 class="card-title">NEKAD ÅTKOMST!</h1>
        <p class="card-subtitle">Du har inte tillgång till denna sida. Eller så har något gått fel. Återkommer problemet så får du ta kontakt med administratören för vidare åtgärd.</p>
        <?= returnBtnHistory() ?>
     </div>
  </main>
  <?php
  _footer();
  exit;
}