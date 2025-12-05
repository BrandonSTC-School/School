<?php
// Start session to track login state
require_once 'session.php';

// Load PDO database connection
require_once 'dbConnection.php';

// Sign up
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        echo $twig->render('signup.twig', [
            'error' => 'Security validation failed. Please try again.'
        ]);
        exit;
    }

    // Backend captcha validation
    if (empty($_POST['not_alien'])) {
        echo $twig->render('signup.twig', [
            'error' => "Please confirm you are not an alien 🛸"
        ]);
        exit;
    }

    // Retrieve, sanitize, and length-limit input values
    $name = substr(filter_var(trim($_POST['name'] ?? ''), FILTER_SANITIZE_STRING), 0, 50);
    $surname = substr(filter_var(trim($_POST['surname'] ?? ''), FILTER_SANITIZE_STRING), 0, 50);
    $email = substr(filter_var(trim($_POST['eMail'] ?? ''), FILTER_SANITIZE_EMAIL), 0, 255);
    $password = $_POST['password'] ?? '';
    $secret_question = substr(filter_var(trim($_POST['secret_question'] ?? ''), FILTER_SANITIZE_STRING), 0, 255);
    $secret_answer = substr(trim($_POST['secret_answer'] ?? ''), 0, 255);

    // Validate required fields
    if (empty($name) || empty($surname) || empty($email) || empty($password) || empty($secret_question) || empty($secret_answer)) {
        echo $twig->render('signup.twig', ['error' => 'All fields are required.']);
        exit;
    }

    // Validate name and surname (letters, spaces, hyphens)
    if (!preg_match("/^[a-zA-Z-' ]+$/", $name) || !preg_match("/^[a-zA-Z-' ]+$/", $surname)) {
        echo $twig->render('signup.twig', ['error' => 'Name and surname can only contain letters, spaces, and hyphens.']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo $twig->render('signup.twig', ['error' => 'Please enter a valid email address.']);
        exit;
    }

    // Enforce strong password rules
    // - ≥8 characters
    // - Uppercase, lowercase, number, special character
    $passwordPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    if (!preg_match($passwordPattern, $password)) {
        echo $twig->render('signup.twig', [
            'error' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.'
        ]);
        exit;
    }

    // Check if email already exists in database
    $stmt = $pdo->prepare("SELECT id FROM user WHERE eMail = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo $twig->render('signup.twig', ['error' => 'Email already registered.']);
        exit;
    }

    // Hash sensitive information
    // - password is hashed
    // - secret answer (for password recovery) is hashed
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $hashedSecret = password_hash($secret_answer, PASSWORD_DEFAULT);

    // Insert new user into database
    // Save secret question in plain text
    // Save secret answer hashed
    $stmt = $pdo->prepare("INSERT INTO user (name, surname, eMail, password, secret_question, secret) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $surname, $email, $hashedPassword, $secret_question, $hashedSecret]);

    // Redirect to login page with success message
    header("Location: login.php?registered=1");
    exit;
}

// If request is GET → load signup form
echo $twig->render('signup.twig');
?>
