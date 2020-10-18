<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_ID'])) {
    // User not logged in, reroute to login page
    $_SESSION['msg'] = "You must log in first";
    header('location: /login');
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['idAdmin']);
    unset($_SESSION['user_ID']);
    unset($_SESSION['user_email']);
    header("location: /login");
    exit();
}

header("location: /request");
exit();

?>
