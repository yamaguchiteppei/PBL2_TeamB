<?php
require __DIR__ . '/php/auth.php';
require_login();

$seller = $_GET['seller'] ?? '';
$book   = $_GET['book'] ?? '';

if ($seller === '' || $book === '') {
    header("Location: book_list.php");
    exit;
}

$chat_file = __DIR__ . '/chat_log.json';
if (!file_exists($chat_file)) {
    file_put_contents($chat_file, json_encode([], JSON_UNESCAPED_UNICODE));
}

$chat_data = json_decode(file_get_contents($chat_file), true) ?? [];

// 🔑 キーは send_message.php / message_list.php と完全一致
$key = "{$seller}_{$book}";

// ✅ 既存チャットは絶対に消さない
if (!isset($chat_data[$key])) {
    $chat_data[$key] = [];
    file_put_contents(
        $chat_file,
        json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );
}

// チャット画面へ
header("Location: message_list.php?seller=" . urlencode($seller) . "&book=" . urlencode($book));
exit;
