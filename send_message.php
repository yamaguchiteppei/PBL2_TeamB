<?php
require __DIR__ . '/php/auth.php';
require_login();

header("Content-Type: application/json; charset=utf-8");

$chat_key = $_POST['chat_key'] ?? '';
$message  = trim($_POST['message'] ?? '');

if ($chat_key === '' || $message === '') {
    echo json_encode(["error" => "invalid_params"]);
    exit;
}

$chat_file = __DIR__ . "/chat_log.json";
if (!file_exists($chat_file)) {
    file_put_contents($chat_file, json_encode([], JSON_UNESCAPED_UNICODE));
}

$chat_data = json_decode(file_get_contents($chat_file), true) ?? [];

$parts = explode('_', $chat_key, 3);
if (count($parts) !== 3) {
    echo json_encode(["error" => "invalid_chat_key"]);
    exit;
}

[$seller, $buyer, $book] = $parts;

$me = $_SESSION['user']['username'];
if ($me !== $seller && $me !== $buyer) {
    http_response_code(403);
    exit;
}

$new_message = [
    "sender" => $me,
    "text"   => $message,
    "time"   => date("Y-m-d H:i:s"),
    "read"   => false
];

$chat_data[$chat_key][] = $new_message;

file_put_contents(
    $chat_file,
    json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);

echo json_encode([
    "status" => "success",
    "message" => $new_message
]);
