<?php
// print __FILE__;
// auth.php
session_start();


function require_login2() {
    if (!empty($_SESSION['user_id'])) {
        return true;
        // return false;
        // header('Location: login.php');
        // print __FUNCTION__;
        // include_once ROOTPATH . "pages/login.php";
        // exit;
    }
    return false;
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        // return false;
        // header('Location: login.php');
        // print __FUNCTION__;
        include_once ROOTPATH . "pages/login.php";
        exit;
    }
    // return true;
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_user_name() {
    return $_SESSION['fullname'] ?? '';
}

function is_logged_in() {
    return current_user_id();
}
