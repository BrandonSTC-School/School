<?php

// AUTOLOADER (Twig, Composer packages)
require_once __DIR__ . '/../vendor/autoload.php';

// TWIG TEMPLATE ENGINE SETUP
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');

$twig = new \Twig\Environment($loader, [
    'cache' => false,
    'debug' => true
]);

// LOGIN SECURITY SETTINGS
// Maximum login attempts allowed before locking account
define('MAX_LOGIN_ATTEMPTS', 3);

// Time window in which failed attempts are counted (seconds)
define('ATTEMPT_WINDOW', 300); // 5 minutes

// Duration of account lock after exceeding attempts
define('LOCKOUT_DURATION', 900); // 15 minutes
