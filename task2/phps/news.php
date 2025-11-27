<?php
require_once 'session.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ----------------------------------------------
// ✅ Handle filters
// ----------------------------------------------
$publisher = $_GET['publisher'] ?? '';
$period    = $_GET['period'] ?? 'week';
$search    = trim($_GET['search'] ?? '');

// Base API endpoint
$apiUrl = "https://api.spaceflightnewsapi.net/v4/articles/?limit=12&ordering=-published_at";

// Add general keyword search (title, summary, etc.)
if ($search !== '') {
    $apiUrl .= "&search=" . urlencode($search);
}

// Filter by specific publisher (SpaceX, NASA, ESA, etc.)
if ($publisher !== '') {
    $apiUrl .= "&news_site=" . urlencode($publisher);
}

// Filter by date range
$days = match ($period) {
    'day' => 1,
    'week' => 7,
    'month' => 30,
    default => 7
};
$fromDate = date('Y-m-d', strtotime("-$days days"));
$apiUrl .= "&published_at_gte=$fromDate";

// ----------------------------------------------
// ✅ Fetch or cache response
// ----------------------------------------------
$cacheDir = __DIR__ . '/../cache/';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);

$cacheKey = md5($apiUrl); // cache per filter combination
$cacheFile = $cacheDir . "news_$cacheKey.json";
$cacheTime = 60 * 60 * 3; // 3 hours

$response = null;
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    $response = file_get_contents($cacheFile);
} else {
    $response = @file_get_contents($apiUrl);
    if ($response) file_put_contents($cacheFile, $response);
}

// ----------------------------------------------
// ✅ Parse API response
// ----------------------------------------------
$articles = [];
$error = null;

if ($response) {
    $data = json_decode($response, true);
    if (isset($data['results'])) {
        $articles = $data['results'];
    } else {
        $error = "No news articles found.";
    }
} else {
    $error = "Failed to contact Spaceflight News API.";
}

// ----------------------------------------------
// ✅ Render Twig template
// ----------------------------------------------
echo $twig->render('news.twig', [
    'articles'  => $articles,
    'error'     => $error,
    'publisher' => $publisher,
    'period'    => $period,
    'search'    => $search
]);
?>