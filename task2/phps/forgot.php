<?php
// Start session so we can store recovery and user state
require_once 'session.php';

// Load PDO database connection
require_once 'dbConnection.php';

// Always generate CSRF token for GET form rendering
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    generateCsrfToken();
}

// Check if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        echo $twig->render('forgot.twig', [
            'error' => 'Security validation failed. Please try again.'
        ]);
        exit;
    }

    // Determine step in recovery flow (verify or reset)
    $step = $_POST['step'] ?? 'verify';

    // STEP 1: Verify email + secret answer
    if ($step === 'verify') {

        // Get submitted email and secret answer
        $email = trim($_POST['eMail'] ?? '');
        $secret_answer = $_POST['secret_answer'] ?? '';

        // Validate both fields are present
        if (empty($email) || empty($secret_answer)) {
            echo $twig->render('forgot.twig', ['error' => 'Please provide both email and answer.']);
            exit;
        }

        // Look up user by email and fetch secret question + hashed answer
        $stmt = $pdo->prepare("SELECT id, secret_question, secret FROM user WHERE eMail = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // If no user found
        if (!$user) {
            echo $twig->render('forgot.twig', ['error' => 'No account found with that email.']);
            exit;
        }

        // Ensure the account has a secret question configured
        if (empty($user['secret']) || empty($user['secret_question'])) {
            echo $twig->render('forgot.twig', ['error' => 'No secret question/answer set for this account.']);
            exit;
        }

        // Check secret answer against stored hashed answer
        if (!password_verify($secret_answer, $user['secret'])) {
            echo $twig->render('forgot.twig', ['error' => 'Incorrect answer.']);
            exit;
        }

        // Correct answer — store user id in session to allow password reset
        $_SESSION['reset_user_id'] = $user['id'];

        // Show password reset form
        echo $twig->render('reset.twig');
        exit;
    }

    // STEP 2: Reset password form submission
    if ($step === 'reset') {

        // Ensure we have a valid session reset token
        if (!isset($_SESSION['reset_user_id'])) {
            echo $twig->render('forgot.twig', ['error' => 'Session expired or invalid. Start again.']);
            exit;
        }

        // Get new password fields
        $newPassword = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Validate both fields filled
        if (empty($newPassword) || empty($confirm)) {
            echo $twig->render('reset.twig', ['error' => 'Please fill both password fields.']);
            exit;
        }

        // Check passwords match
        if ($newPassword !== $confirm) {
            echo $twig->render('reset.twig', ['error' => 'Passwords do not match.']);
            exit;
        }

        // Load current password hash to prevent reusing old password
        $stmt = $pdo->prepare("SELECT password FROM user WHERE id = ?");
        $stmt->execute([$_SESSION['reset_user_id']]);
        $user = $stmt->fetch();

        // Prevent setting same password as before
        if ($user && password_verify($newPassword, $user['password'])) {
            echo $twig->render('reset.twig', ['error' => 'New password cannot be the same as the old one.']);
            exit;
        }

        // Enforce strong password requirements
        $passwordPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
        if (!preg_match($passwordPattern, $newPassword)) {
            echo $twig->render('reset.twig', ['error' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.']);
            exit;
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update user's password in DB
        $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $_SESSION['reset_user_id']]);

        // Remove reset session variable
        unset($_SESSION['reset_user_id']);

        // Show success message
        echo $twig->render('reset.twig', ['success' => 'Password has been reset. You can now log in.']);
        exit;
    }
}

// Initial GET request → show email + secret answer form
echo $twig->render('forgot.twig');
?>