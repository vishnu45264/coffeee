<?php
// Include necessary files
require_once 'config/database.php';
require_once 'include/functions.php';

// Start session if not already started
session_start_safe();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Set alert message for next page
session_start();
$_SESSION['alert'] = showAlert("You have been successfully logged out.", "info");

// Redirect to login page
header("Location: index.php");
exit;
?> 