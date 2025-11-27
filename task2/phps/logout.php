<?php
// Start session so we can clear it
require_once 'session.php';

// Remove all session variables (logout the user)
session_unset();

// Destroy the session entirely
session_destroy();

// Redirect user back to the homepage after logout
header("Location: ../index.php");

// Stop further script execution
exit;
?>