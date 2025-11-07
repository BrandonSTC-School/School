<?php
// Start session to check login status and access user data
require_once 'phps/session.php';

// Load database connection
require_once 'phps/dbConnection.php';

// --------------------------------------------------
// Access Control: Prevent access if user is not logged in
// --------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    // Redirect user to login page if no session exists
    header("Location: phps/login.php");
    exit;
}

// --------------------------------------------------
// Render dashboard Twig template and pass user info
// --------------------------------------------------
echo $twig->render('dashboard.twig', [
    'user_name'    => $_SESSION['user_name'],    // First name from session
    'user_surname' => $_SESSION['user_surname'], // Surname from session
]);
?>