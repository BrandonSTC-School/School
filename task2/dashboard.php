<?php
// Session and dependencies
require_once 'phps/session.php';
require_once 'phps/dbConnection.php';

// Access Control: Prevent access if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: phps/login.php");
    exit;
}

// Render dashboard Twig template and pass user info
echo $twig->render('dashboard.twig', [
    'user_name'    => $_SESSION['user_name']    ?? '',
    'user_surname' => $_SESSION['user_surname'] ?? '',
]);
?>