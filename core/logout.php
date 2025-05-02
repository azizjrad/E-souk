<?php
session_start();
require_once  '../config/init.php';

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page with proper path
header("Location: " . ROOT_URL . "public/pages/index.php");
exit();
?>