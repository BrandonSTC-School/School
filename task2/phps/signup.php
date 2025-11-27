<?php
// Start session to track login state
require_once 'session.php';
// Load PDO database connection
require_once 'dbConnection.php';

// --------------------------------------------------
// Process form submission when user hits "Sign Up"
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Backend captcha validation
    if (empty($_POST['not_alien'])) {
        echo $twig->render('signup.twig', [
            'error' => "Please confirm you are not an alien 🛸"
        ]);
        exit;
    }

    // Retrieve and sanitize input values
    $name            = trim($_POST['name']);
    $surname         = trim($_POST['surname']);
    $email           = trim($_POST['eMail']);
    $password        = $_POST['password'];
    $secret_question = trim($_POST['secret_question'] ?? '');
    $secret_answer   = $_POST['secret_answer'] ?? '';

    // --------------------------------------------------
    // Validate required fields (none should be empty)
    // --------------------------------------------------
    if (empty($name) || empty($surname) || empty($email) || empty($password) || empty($secret_question) || empty($secret_answer)) {
        echo $twig->render('signup.twig', ['error' => 'All fields are required.']);
        exit;
    }

    // --------------------------------------------------
    // Validate name and surname (letters, spaces, hyphens)
    // --------------------------------------------------
    if (!preg_match("/^[a-zA-Z-' ]+$/", $name) || !preg_match("/^[a-zA-Z-' ]+$/", $surname)) {
        echo $twig->render('signup.twig', ['error' => 'Name and surname can only contain letters, spaces, and hyphens.']);
        exit;
    }

    // --------------------------------------------------
    // Validate email format
    // --------------------------------------------------
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo $twig->render('signup.twig', ['error' => 'Please enter a valid email address.']);
        exit;
    }

    // --------------------------------------------------
    // Enforce strong password rules
    // - ≥8 characters
    // - Uppercase, lowercase, number, special character
    // --------------------------------------------------
    $passwordPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    if (!preg_match($passwordPattern, $password)) {
        echo $twig->render('signup.twig', [
            'error' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.'
        ]);
        exit;
    }

    // --------------------------------------------------
    // Check if email already exists in database
    // --------------------------------------------------
    $stmt = $pdo->prepare("SELECT id FROM user WHERE eMail = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        echo $twig->render('signup.twig', ['error' => 'Email already registered.']);
        exit;
    }

    // --------------------------------------------------
    // Hash sensitive information
    // - password is hashed
    // - secret answer (for password recovery) is hashed
    // --------------------------------------------------
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $hashedSecret   = password_hash($secret_answer, PASSWORD_DEFAULT);

    // --------------------------------------------------
    // Insert new user into database
    // Save secret question in plain text
    // Save secret answer hashed
    // --------------------------------------------------
    $stmt = $pdo->prepare("INSERT INTO user (name, surname, eMail, password, secret_question, secret) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $surname, $email, $hashedPassword, $secret_question, $hashedSecret]);

    // --------------------------------------------------
    // Redirect to login page with success message
    // ?registered=1 triggers a "success" message on login form
    // --------------------------------------------------
    header("Location: login.php?registered=1");
    exit;
}

// --------------------------------------------------
// If request is GET -> load signup form
// --------------------------------------------------
echo $twig->render('signup.twig');
?>