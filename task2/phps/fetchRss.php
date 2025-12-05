<?php
function fetchRssFeed(string $url, string $sourceName): array
{
    $context = stream_context_create([
        'http' => ['timeout' => 5]
    ]);

    $xml = @simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA, '', true);
    
    if (!$xml) {
        return []; // fail silently
    }

    $articles = [];

    foreach ($xml->channel->item as $item) {
        $articles[] = [
            'title'        => (string)$item->title,
            'summary'      => strip_tags((string)$item->description),
            'url'          => (string)$item->link,
            'imageUrl'     => isset($item->enclosure['url']) ? (string)$item->enclosure['url'] : null,
            'publishedAt'  => date('Y-m-d H:i:s', strtotime((string)$item->pubDate)),
            'source'       => $sourceName
        ];
    }

    return $articles;
}
