<?php
/**
 *  Global Configuration File
 *  - Loads Twig
 *  - Defines security headers (CSP)
 *  - Defines login security constants
 */


// 1) SECURITY HEADERS (Fixes ZAP CSP warnings, test ran on 3/12/2025)
header("Content-Security-Policy:
    default-src 'self';
    img-src 'self' data: https:;
    style-src 'self' 'unsafe-inline';
    script-src 'self' 'unsafe-inline';
    font-src 'self' https:;
    connect-src 'self' https:;
    frame-ancestors 'none';
");

// 2) AUTOLOADER (Twig, Composer packages)
require_once __DIR__ . '/../vendor/autoload.php';

// 3) TWIG TEMPLATE ENGINE SETUP
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');

$twig = new \Twig\Environment($loader, [
    'cache' => false,  // Disable cache during development
    'debug' => true    // Enables dump() and debugging tools
]);

// 4) LOGIN SECURITY SETTINGS

// Maximum login attempts allowed before locking account
define('MAX_LOGIN_ATTEMPTS', 3);

// Time window in which failed attempts are counted (seconds)
define('ATTEMPT_WINDOW', 300); // 5 minutes

// Duration of account lock after exceeding attempts
define('LOCKOUT_DURATION', 900); // 15 minutes
