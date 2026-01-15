<?php
require __DIR__ . '/php/auth.php';
require_login();

header("Content-Type: application/json; charset=utf-8");

$chat_file = __DIR__ . "/chat_log.json";
$chat_data = json_decode(file_get_contents($chat_file), true) ?? [];

$seller = $_GET['seller'] ?? '';
$book   = $_GET['book'] ?? '';

$key = "{$seller}_{$book}";
$messages = $chat_data[$key] ?? [];

// 既読に更新
foreach ($messages as &$m) {
    if ($m['sender'] !== $_SESSION['user']['username']) {
        $m['read'] = true;
    }
}
unset($m);

file_put_contents($chat_file, json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo json_encode($messages);
