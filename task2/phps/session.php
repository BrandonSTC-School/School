<?php

// Always start session first
if (session_status() === PHP_SESSION_NONE) {

    // Configure session cookie parameters to fix HttpOnly alert
    session_set_cookie_params([
        'lifetime' => 0, // Session expires when browser closes
        'path' => '/', // Available across the site
        'domain' => $_SERVER['HTTP_HOST'], // Current host
        'secure' => isset($_SERVER['HTTPS']), // Only send over HTTPS if available
        'httponly' => true, // Prevent JavaScript access
        'samesite' => 'Lax', // Helps protect against CSRF
    ]);

    session_start();
}

// Load Twig BEFORE using addGlobal
require_once __DIR__ . '/config.php';

// CSRF PROTECTION HELPERS
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Make token available inside Twig
$twig->addGlobal('csrf_token', generateCsrfToken());

// Make login status available to Twig globally
$twig->addGlobal('is_logged_in', isset($_SESSION['user_id']));

// Make current page available globally
$twig->addGlobal('current_page', basename($_SERVER['PHP_SELF']));