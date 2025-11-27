<?php
// Start session to track login state and attempt info
require_once 'session.php';

// Load PDO database connection
require_once 'dbConnection.php';

// Prepare success message (shown after registration)
$successMessage = '';
if (isset($_GET['registered'])) {
    // Display friendly message if user came from successful signup
    $successMessage = 'Registration successful. You can now log in.';
}

// ----------------------------------------------------
// Handle form submission (when login POST request sent)
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Backend captcha validation
    if (empty($_POST['not_alien'])) {
        echo $twig->render('signup.twig', [
            'error' => "Please confirm you are not an alien 🛸"
        ]);
        exit;
    }

    // Get email and password from form, trim whitespace
    $email = trim($_POST['eMail'] ?? '');
    $password = $_POST['password'] ?? '';

    // Ensure both fields are filled in
    if (empty($email) || empty($password)) {
        echo $twig->render('login.twig', ['error' => 'Please fill in both fields.']);
        exit;
    }

    // Lookup user by email
    $stmt = $pdo->prepare("SELECT * FROM user WHERE eMail = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // If no account exists with that email
    if (!$user) {
        echo $twig->render('login.twig', ['error' => 'Invalid email or password.']);
        exit;
    }

    // ----------------------------------------------------
    // Check if account is currently locked due to failures
    // ----------------------------------------------------
    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
        // Calculate how many minutes remain in lockout
        $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);

        echo $twig->render('login.twig', [
            'error' => "Account is locked. Try again in {$remaining} minute(s)."
        ]);
        exit;
    }

    // ----------------------------------------------------
    // Verify entered password against hashed password
    // ----------------------------------------------------
    if (!password_verify($password, $user['password'])) {

        // Get current timestamp
        $now = date('Y-m-d H:i:s');

        // Increment failed attempt count (default to 0 if null)
        $failed = ($user['failed_attempts'] ?? 0) + 1;
        $lockoutTime = null; // Default: no lock

        // Reset counter if last failed attempt was long ago
        if (!empty($user['last_failed']) && (time() - strtotime($user['last_failed'])) > ATTEMPT_WINDOW) {
            $failed = 1; // Restart attempts
        }

        // Lock account if failed attempts exceed allowed threshold
        if ($failed >= MAX_LOGIN_ATTEMPTS) {
            // Set lockout end time
            $lockoutTime = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);

            // Fake email notification shown to user (no real email)
            $fakeEmailMsg = "An email has been sent to the registered address notifying of a login lockout.";

            echo $twig->render('login.twig', [
                'error' => "Account locked due to multiple failed attempts. Try again in " . (LOCKOUT_DURATION / 60) . " minutes.",
                'fakeEmail' => $fakeEmailMsg
            ]);
        } else {
            // Show remaining attempts message
            echo $twig->render('login.twig', [
                'error' => "Incorrect password. Attempt {$failed}/" . MAX_LOGIN_ATTEMPTS . "."
            ]);
        }

        // Update failed attempts and lockout status in DB
        $update = $pdo->prepare("
            UPDATE user SET failed_attempts = ?, last_failed = ?, locked_until = ?
            WHERE id = ?
        ");
        $update->execute([$failed, $now, $lockoutTime, $user['id']]);
        exit;
    }

    // ----------------------------------------------------
    // Successful login: reset lockout counters
    // ----------------------------------------------------
    $update = $pdo->prepare("
        UPDATE user SET failed_attempts = 0, last_failed = NULL, locked_until = NULL
        WHERE id = ?
    ");
    $update->execute([$user['id']]);

    // Store user credentials in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_surname'] = $user['surname'];

    // Redirect to dashboard
    header("Location: ../dashboard.php");
    exit;
}

// ----------------------------------------------------
// Initial page load (no POST)
// Show login form, optionally with registration success message
// ----------------------------------------------------
echo $twig->render('login.twig', ['success' => $successMessage]);
?>