<?php
$chat_file = __DIR__ . '/chat_log.json';
if (!file_exists($chat_file)) file_put_contents($chat_file, json_encode([], JSON_UNESCAPED_UNICODE));
$chat_data = json_decode(file_get_contents($chat_file), true) ?? [];

// ===== メッセージ送信 (POST) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seller  = trim($_POST['seller'] ?? '');
    $book    = trim($_POST['book'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($seller === '' || $book === '' || $message === '') {
        echo json_encode(["status" => "error", "msg" => "invalid input"]);
        exit;
    }

    $key = "{$seller}_{$book}";
    if (!isset($chat_data[$key])) $chat_data[$key] = [];

    $chat_data[$key][] = [
        "sender" => "me",
        "text"   => $message,
        "time"   => date("Y-m-d H:i:s"),
        "read"   => false
    ];

    file_put_contents($chat_file, json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo json_encode(["status" => "ok"]);
    exit;
}

// ===== チャット履歴読み込み =====
if (isset($_GET['load_chat'])) {
    $key = $_GET['load_chat'];
    echo json_encode($chat_data[$key] ?? []);
    exit;
}

// ===== 未読数リスト =====
if (isset($_GET['status']) && $_GET['status'] === 'list') {
    $counts = [];
    foreach ($chat_data as $key => $messages) {
        $unread = 0;
        foreach ($messages as $m) {
            if ($m['sender'] !== 'me' && (empty($m['read']) || !$m['read'])) $unread++;
        }
        $counts[$key] = $unread;
    }
    echo json_encode($counts);
    exit;
}

// ===== 既読化処理 =====
if (isset($_GET['mark_read'])) {
    $key = $_GET['mark_read'];
    if (isset($chat_data[$key])) {
        foreach ($chat_data[$key] as &$m) {
            if ($m['sender'] !== 'me') $m['read'] = true;
        }
        unset($m);
        file_put_contents($chat_file, json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    echo json_encode(["status" => "ok"]);
    exit;
}

echo json_encode(["status" => "error", "msg" => "no action"]);
?>