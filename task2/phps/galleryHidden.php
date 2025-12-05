<?php
require_once 'session.php';
require_once 'dbConnection.php';

// Ensure user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// ----------------------------------------------
// Retrieve PRG success message (if any)
// ----------------------------------------------
$message = "";
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// ----------------------------------------------
// ✅ Restore image (set status = 1)
// ----------------------------------------------
if (isset($_GET['restore'])) {
    $imgId = (int) $_GET['restore'];

    $stmt = $pdo->prepare("UPDATE gallery SET status = 1 WHERE id = ? AND userId = ?");
    $stmt->execute([$imgId, $userId]);

    $_SESSION['success_message'] = "✅ Image restored";
    header("Location: galleryHidden.php");
    exit;
}

// ----------------------------------------------
// ✅ Search & Sort functionality
// ----------------------------------------------
$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort'] ?? 'newest';

$orderBy = "createdAt DESC"; // newest first by default
if ($sort === "oldest")      $orderBy = "createdAt ASC";
if ($sort === "title_asc")   $orderBy = "title ASC";
if ($sort === "title_desc")  $orderBy = "title DESC";

// Base query: only hidden images for logged-in user
$query = "
    SELECT id, title, filePath, userId
    FROM gallery
    WHERE status = 0 AND userId = ?
";

$params = [$userId];

// Add search filter
if (!empty($search)) {
    $query .= " AND title LIKE ?";
    $params[] = "%$search%";
}

$query .= " ORDER BY $orderBy";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$hiddenImages = $stmt->fetchAll();

// ----------------------------------------------
// ✅ Render template
// ----------------------------------------------
echo $twig->render('galleryHidden.twig', [
    'images'  => $hiddenImages,
    'success' => $message,
    'search'  => $search,
    'sort'    => $sort
]);
?>