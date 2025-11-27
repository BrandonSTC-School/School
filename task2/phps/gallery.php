<?php
// --------------------------------------------------
// Session and database setup
// --------------------------------------------------
require_once 'session.php';
require_once 'dbConnection.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Logged-in user ID
$userId = $_SESSION['user_id'];

// Variables for flash and error messages
$error = "";
$message = "";

// --------------------------------------------------
// ✅ Retrieve and clear flash message (Post-Redirect-Get pattern)
// --------------------------------------------------
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// --------------------------------------------------
// ✅ Handle image upload (File-based storage)
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $file  = $_FILES['image'] ?? null;

    // Validate inputs
    if (empty($title) || !$file || $file['error'] !== UPLOAD_ERR_OK) {
        $error = "Please enter a title and select an image.";
    } else {

        // ✅ Create /uploads directory if missing
        $mainUploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($mainUploadDir)) {
            mkdir($mainUploadDir, 0777, true);
        }

        // ✅ Create user-specific subfolder (uploads/user_<id>/)
        $userUploadDir = $mainUploadDir . "user_$userId/";
        if (!is_dir($userUploadDir)) {
            mkdir($userUploadDir, 0777, true);
        }

        // ✅ Validate image extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = "Unsupported image format. Please use JPG, PNG, GIF, or WEBP.";
        } else {
            // Generate unique filename
            $uniqueName   = uniqid("astro_", true) . "." . $ext;
            $targetPath   = $userUploadDir . $uniqueName;
            $relativePath = "uploads/user_$userId/" . $uniqueName;

            // ✅ Move uploaded file safely
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                try {
                    // Insert metadata into the database
                    $stmt = $pdo->prepare("
                        INSERT INTO gallery (userId, title, filePath, createdAt, status)
                        VALUES (?, ?, ?, NOW(), 1)
                    ");
                    $stmt->execute([$userId, $title, $relativePath]);

                    // Flash success message and redirect (PRG)
                    $_SESSION['success_message'] = "✅ Image uploaded successfully!";
                    header("Location: " . $_SERVER['REQUEST_URI'] . "?t=" . time());
                    exit;


                } catch (Exception $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            } else {
                $error = "Failed to move uploaded file.";
            }
        }
    }
}

// --------------------------------------------------
// ✅ Soft delete / hide image
// --------------------------------------------------
if (isset($_GET['hide'])) {
    $imgId = (int) $_GET['hide'];

    $stmt = $pdo->prepare("UPDATE gallery SET status = 0 WHERE id = ? AND userId = ?");
    $stmt->execute([$imgId, $userId]);

    $_SESSION['success_message'] = "🕶 Image hidden";
    header("Location: gallery.php");
    exit;
}

// --------------------------------------------------
// ✅ Search & Sort functionality
// --------------------------------------------------
$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort'] ?? 'newest';

$query  = "SELECT id, title, filePath, userId FROM gallery WHERE status = 1";
$params = [];

if ($search !== '') {
    $query .= " AND title LIKE ?";
    $params[] = "%$search%";
}

// Sorting options
switch ($sort) {
    case 'oldest':      $query .= " ORDER BY createdAt ASC"; break;
    case 'title_asc':   $query .= " ORDER BY title ASC"; break;
    case 'title_desc':  $query .= " ORDER BY title DESC"; break;
    default:            $query .= " ORDER BY createdAt DESC"; break;
}

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$images = $stmt->fetchAll();

// --------------------------------------------------
// ✅ Render Twig template
// --------------------------------------------------
echo $twig->render('gallery.twig', [
    'images'  => $images,
    'user_id' => $userId,
    'error'   => $error,
    'success' => $message
]);
?>