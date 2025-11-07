<?php
// Load Composer autoloader to include Twig and any other libraries installed via Composer
require_once __DIR__ . '/../vendor/autoload.php';

// -------------------------
// Twig Template Engine Setup
// -------------------------

// Tell Twig where your Twig template files are located
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');

// Create the Twig environment instance
$twig = new \Twig\Environment($loader, [
    'cache' => false, // Disable caching for development (enable in production for speed)
    'debug' => true   // Enable debug mode (allows using Twig debug features)
]);

// -------------------------
// Security / Login Settings
// -------------------------

// Maximum number of allowed failed login attempts before temporary lockout
define('MAX_LOGIN_ATTEMPTS', 3);

// Duration of the window in which failed login attempts are counted (in seconds)
// e.g., 300 seconds = 5 minutes
define('ATTEMPT_WINDOW', 300);

// Duration of lockout period after too many failed attempts (in seconds)
// e.g., 900 seconds = 15 minutes
define('LOCKOUT_DURATION', 900);
?>