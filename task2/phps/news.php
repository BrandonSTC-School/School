<?php
require_once 'session.php';
require_once 'dbConnection.php';
require_once 'fetchRss.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Filters
$publisher = $_GET['publisher'] ?? '';
$period = $_GET['period'] ?? 'week';
$search = trim($_GET['search'] ?? '');
$limit = 12;

// Determine date window
$days = match ($period) {
    'day' => 1,
    'week' => 7,
    'month' => 30,
    default => 7
};
$fromDate = date('Y-m-d', strtotime("-$days days"));

// Fetch Spaceflight News API
$apiUrl = "https://api.spaceflightnewsapi.net/v4/articles/?limit=100&ordering=-published_at";

if ($search !== '') {
    $apiUrl .= "&search=" . urlencode($search);
}
if ($publisher !== '') {
    $apiUrl .= "&news_site=" . urlencode($publisher);
}
$apiUrl .= "&published_at_gte=$fromDate";

$sfResponse = @file_get_contents($apiUrl);
$sfArticles = [];

if ($sfResponse) {
    $json = json_decode($sfResponse, true);
    if (isset($json['results'])) {
        foreach ($json['results'] as $item) {
            $sfArticles[] = [
                'title' => $item['title'],
                'summary' => $item['summary'],
                'url' => $item['url'],
                'imageUrl' => $item['image_url'],
                'publishedAt' => $item['published_at'],
                'source' => $item['news_site'] ?? 'Spaceflight News'
            ];
        }
    }
}

// Fetch RSS Feeds
$nasaArticles = fetchRssFeed("https://www.nasa.gov/rss/dyn/breaking_news.rss", "NASA");
$esaArticles = fetchRssFeed("https://www.esa.int/rssfeed/Our_Activities", "ESA");

// Merge + filter + sort
$allArticles = array_merge($sfArticles, $nasaArticles, $esaArticles);

// Search filter
if ($search !== '') {
    $allArticles = array_filter($allArticles, function ($a) use ($search) {
        return stripos($a['title'], $search) !== false || stripos($a['summary'], $search) !== false;
    });
}

// Date filter
$allArticles = array_filter($allArticles, function ($a) use ($fromDate) {
    return strtotime($a['publishedAt']) >= strtotime($fromDate);
});

// Sort newest → oldest
usort($allArticles, function ($a, $b) {
    return strtotime($b['publishedAt']) - strtotime($a['publishedAt']);
});

// Initial batch
$initialArticles = array_slice($allArticles, 0, $limit);

// Render Twig
echo $twig->render('news.twig', [
    'articles' => $initialArticles,
    'filters' => [
        'publisher' => $publisher,
        'period' => $period,
        'search' => $search
    ],
    'total' => count($allArticles)
]);