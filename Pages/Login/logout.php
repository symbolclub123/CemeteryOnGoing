<?php
session_start();

// Unset all of the session variables
$_SESSION = array();

// If the session is set, destroy it
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Finally, destroy the session
session_destroy();

// Redirect to the login page if the user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
