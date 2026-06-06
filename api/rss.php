<?php
/**
 * RSSプロキシ - はてなブログのRSSを中継
 * 
 * 使い方: /api/rss.php?url=https://avakobe.hatenablog.com/rss
 * 
 * ブラウザのCORS制限を回避するために、
 * サーバー側で外部RSSを取得してそのまま返します。
 * セキュリティ対策として、はてなブログのRSSのみ許可しています。
 */

// キャッシュ制御: 5分間キャッシュ
header('Cache-Control: public, max-age=300');
header('Access-Control-Allow-Origin: *');

// URLパラメータを取得
$rssUrl = isset($_GET['url']) ? $_GET['url'] : '';

// バリデーション
if (empty($rssUrl)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'url parameter is required']);
    exit;
}

// はてなブログのRSSのみ許可（セキュリティ対策）
if (strpos($rssUrl, 'hatenablog.com/') === false) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Only hatenablog.com RSS feeds are allowed']);
    exit;
}

// RSSを取得
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: AVA-Website-RSS-Reader/1.0\r\n",
        'timeout' => 10
    ]
]);

$xml = @file_get_contents($rssUrl, false, $context);

if ($xml === false) {
    http_response_code(502);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Failed to fetch RSS feed']);
    exit;
}

// XMLをそのまま返す
header('Content-Type: application/xml; charset=utf-8');
echo $xml;
