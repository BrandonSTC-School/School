<?php
// Start session to access logged-in user info
require_once 'session.php';

// Load PDO database connection
require_once 'dbConnection.php';

// If user is not logged in, redirect them to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch current logged-in user's info from database
$stmt = $pdo->prepare("SELECT name, surname, eMail FROM user WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Prepare variables for feedback messages
$message = '';
$error = '';

// Check if form was submitted (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get submitted form values, trim to remove extra spaces
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['eMail'] ?? '');
    $password = $_POST['password'] ?? ''; // Password may be empty

    // Validate required fields (password optional)
    if (empty($name) || empty($surname) || empty($email)) {
        $error = 'All fields except password are required.';

    // Validate email format
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';

    } else {

        // Check if email already exists for another user
        $stmt = $pdo->prepare("SELECT id FROM user WHERE eMail = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);

        // If a result is found, email is already used
        if ($stmt->fetch()) {
            $error = 'Email already in use.';
        } else {

            // If password field was filled, validate complexity & update
            if ($password !== '') {

                // Password must meet security rules
                $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

                if (!preg_match($pattern, $password)) {
                    $error = 'Password must be at least 8 chars and include uppercase, lowercase, number, special char.';
                } else {
                    // Hash the new password securely
                    $hashed = password_hash($password, PASSWORD_DEFAULT);

                    // Update all fields including password
                    $update = $pdo->prepare("UPDATE user SET name=?, surname=?, eMail=?, password=? WHERE id=?");
                    $update->execute([$name, $surname, $email, $hashed, $_SESSION['user_id']]);

                    $message = "Account updated successfully!";
                }

            // If password left empty, update all fields except password
            } else {
                $update = $pdo->prepare("UPDATE user SET name=?, surname=?, eMail=? WHERE id=?");
                $update->execute([$name, $surname, $email, $_SESSION['user_id']]);

                $message = "Account updated successfully!";
            }

            // Update $user array so new data is shown in form immediately
            $user = ['name' => $name, 'surname' => $surname, 'eMail' => $email];
        }
    }
}

// Render Twig template and pass variables to it
echo $twig->render('account.twig', [
    'user' => $user, // Current user data
    'success' => $message, // Success message if any
    'error' => $error // Error message if any
]);
?>