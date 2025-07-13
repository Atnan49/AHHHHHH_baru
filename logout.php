<?php
require_once 'includes/functions.php';

// Destroy session
session_start();
session_destroy();

// Clear cookies if any
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// Redirect ke halaman utama
redirect('index.php');
?>
