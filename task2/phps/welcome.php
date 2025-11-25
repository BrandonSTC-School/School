<?php
require_once 'session.php';
require_once 'dbConnection.php';

echo $twig->render('welcome.twig', [
    'is_logged_in' => isset($_SESSION['user_id']),
]);