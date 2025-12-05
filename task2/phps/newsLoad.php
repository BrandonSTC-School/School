<?php
require_once 'fetchRss.php';

$offset = intval($_GET['offset'] ?? 0);
$limit  = 12;

$publisher = $_GET['publisher'] ?? '';
$period    = $_GET['period'] ?? 'week';
$search    = trim($_GET['search'] ?? '');

// Determine date range
$days = match ($period) {
    'day'   => 1,
    'week'  => 7,
    'month' => 30,
    default => 7
};
$fromDate = date('Y-m-d', strtotime("-$days days"));

// Fetch Spaceflight News
$sfJson = @file_get_contents("https://api.spaceflightnewsapi.net/v4/articles/?limit=100&ordering=-published_at");
$sfItems = $sfJson ? json_decode($sfJson, true)['results'] : [];

$sfArticles = [];
foreach ($sfItems as $item) {
    $sfArticles[] = [
        'title'       => $item['title'],
        'summary'     => $item['summary'],
        'url'         => $item['url'],
        'imageUrl'    => $item['image_url'],
        'publishedAt' => $item['published_at'],
        'source'      => $item['news_site']
    ];
}

// Fetch RSS feeds
$nasaArticles = fetchRssFeed("https://www.nasa.gov/rss/dyn/breaking_news.rss", "NASA");
$esaArticles  = fetchRssFeed("https://www.esa.int/rssfeed/Our_Activities", "ESA");

$allArticles = array_merge($sfArticles, $nasaArticles, $esaArticles);

// Apply filters
if ($search !== '') {
    $allArticles = array_filter($allArticles, fn($a) =>
        stripos($a['title'], $search) !== false ||
        stripos($a['summary'], $search) !== false
    );
}

$allArticles = array_filter($allArticles, fn($a) =>
    strtotime($a['publishedAt']) >= strtotime($fromDate)
);

// Sort newest first
usort($allArticles, fn($a, $b) =>
    strtotime($b['publishedAt']) - strtotime($a['publishedAt'])
);

// Slice chunk
$chunk = array_slice($allArticles, $offset, $limit);

// Output JSON
header('Content-Type: application/json');
echo json_encode($chunk);
