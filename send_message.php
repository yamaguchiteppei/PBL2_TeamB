<?php
require __DIR__ . '/php/auth.php';
require_login();

header("Content-Type: application/json; charset=utf-8");

// JSONファイルの読み込み
$chat_file = __DIR__ . "/chat_log.json";
if (!file_exists($chat_file)) {
    file_put_contents($chat_file, json_encode([], JSON_UNESCAPED_UNICODE));
}

$chat_data = json_decode(file_get_contents($chat_file), true) ?? [];

// POSTデータの受信
$seller = $_POST['seller'] ?? '';
$book   = $_POST['book'] ?? '';
$message = trim($_POST['message'] ?? '');

if ($seller === '' || $book === '' || $message === '') {
    echo json_encode(["error" => "invalid_params"]);
    exit;
}

// 自分のアカウント
$me = $_SESSION['user']['username'];

// キー
$key = "{$seller}_{$book}";
if (!isset($chat_data[$key])) {
    $chat_data[$key] = [];
}

// 保存するメッセージデータ
$new_message = [
    "sender" => $me,
    "text"   => $message,
    "time"   => date("Y-m-d H:i:s"),
    "read"   => false
];

// 追加
$chat_data[$key][] = $new_message;

// JSON へ保存
file_put_contents($chat_file, json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo json_encode(["status" => "success"]);
