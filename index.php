<?php

// Load Twig configuration (template engine setup)
require_once 'phps/config.php';

// Load database connection (not used here yet but required for future logic)
require_once 'phps/dbConnection.php';

// --------------------------------------------------
// Entry point of the site (homepage)
// Future: this can route to login/dashboard if user is logged in
// --------------------------------------------------

// Render landing/welcome page using Twig template engine
echo $twig->render('welcome.twig', [
    'title'   => 'Welcome to AstroGallery',        // Page heading text
    'message' => 'Explore the cosmos through our community gallery!' // Subtitle text
]);
?>