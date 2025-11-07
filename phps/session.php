<?php

// Always start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Twig BEFORE using addGlobal
require_once __DIR__ . '/config.php';

// Make login status available to Twig globally
$twig->addGlobal('is_logged_in', isset($_SESSION['user_id']));