<?php
require __DIR__ . '/php/auth.php';

header('Content-Type: application/json; charset=utf-8');

// ===== ログイン確認 =====
if (!is_logged_in()) {
    echo json_encode(["status" => "error", "msg" => "not_logged_in"]);
    exit;
}

$current = current_user()['username'];

// ===== チャットログ読み込み =====
$chat_file = __DIR__ . '/chat_log.json';
if (!file_exists($chat_file)) {
    file_put_contents($chat_file, json_encode([], JSON_UNESCAPED_UNICODE));
}
$chat_data = json_decode(file_get_contents($chat_file), true) ?? [];


/* =========================================================
   メッセージ送信 / 通報（POST）
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    /* ---------- 個別メッセージ通報 ---------- */
    if ($action === 'report') {
        $seller = trim($_POST['seller'] ?? '');
        $book   = trim($_POST['book'] ?? '');
        $text   = trim($_POST['text'] ?? '');
        $time   = trim($_POST['time'] ?? '');
        $orig   = trim($_POST['original_sender'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        if ($seller === '' || $book === '' || $text === '') {
            echo json_encode(["status" => "error", "msg" => "invalid input"]);
            exit;
        }

        $reports_file = __DIR__ . '/message_reports.json';
        if (!file_exists($reports_file)) {
            file_put_contents($reports_file, json_encode([], JSON_UNESCAPED_UNICODE));
        }

        $reports = json_decode(file_get_contents($reports_file), true) ?? [];
        $reports[] = [
            "seller"   => $seller,
            "book"     => $book,
            "text"     => $text,
            "time"     => $time,
            "sender"   => $orig,
            "reporter" => $current,
            "reason"   => $reason ?: "（理由なし）",
            "reported_at" => date("Y-m-d H:i:s")
        ];

        file_put_contents(
            $reports_file,
            json_encode($reports, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        echo json_encode(["status" => "ok"]);
        exit;
    }

    /* ---------- 通常メッセージ送信 ---------- */
    $message = trim($_POST['message'] ?? '');
    if ($message === '') {
        echo json_encode(["status" => "error", "msg" => "empty message"]);
        exit;
    }

    // ★ chat_key はセッションのみを信頼
    $chat_key = $_SESSION['current_chat_key'] ?? '';
    if ($chat_key === '') {
        echo json_encode(["status" => "error", "msg" => "no chat key"]);
        exit;
    }

    // chat_key を分解
    [$u1, $u2, $book] = explode('_', $chat_key, 3);

    // 不正アクセス防止
    if ($current !== $u1 && $current !== $u2) {
        echo json_encode(["status" => "error", "msg" => "invalid user"]);
        exit;
    }

    if (!isset($chat_data[$chat_key])) {
        $chat_data[$chat_key] = [];
    }

    $chat_data[$chat_key][] = [
        "sender" => $current,
        "text"   => $message,
        "time"   => date("Y-m-d H:i:s"),
        "read"   => false
    ];

    file_put_contents(
        $chat_file,
        json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );

    echo json_encode(["status" => "ok"]);
    exit;
}


/* =========================================================
   チャット履歴読み込み
========================================================= */
if (isset($_GET['load_chat'])) {
    $key = $_GET['load_chat'];

    if (!isset($chat_data[$key])) {
        echo json_encode([]);
        exit;
    }

    [$u1, $u2] = explode('_', $key, 3);
    if ($current !== $u1 && $current !== $u2) {
        http_response_code(403);
        exit;
    }

    $result = [];
    foreach ($chat_data[$key] as $m) {
        $result[] = [
            "sender" => $m['sender'],
            "text"   => $m['text'],
            "time"   => $m['time'],
            "read"   => $m['read'],
            "is_me"  => ($m['sender'] === $current)
        ];
    }

    echo json_encode($result);
    exit;
}


/* =========================================================
   未読数リスト
========================================================= */
if (isset($_GET['status']) && $_GET['status'] === 'list') {
    $counts = [];

    foreach ($chat_data as $key => $messages) {
        $unread = 0;
        foreach ($messages as $m) {
            if ($m['sender'] !== $current && empty($m['read'])) {
                $unread++;
            }
        }
        $counts[$key] = $unread;
    }

    echo json_encode($counts);
    exit;
}


/* =========================================================
   既読化
========================================================= */
if (isset($_GET['mark_read'])) {
    $key = $_GET['mark_read'];

    if (isset($chat_data[$key])) {
        foreach ($chat_data[$key] as &$m) {
            if ($m['sender'] !== $current) {
                $m['read'] = true;
            }
        }
        unset($m);

        file_put_contents(
            $chat_file,
            json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    echo json_encode(["status" => "ok"]);
    exit;
}


/* ========================================================= */
echo json_encode(["status" => "error", "msg" => "no action"]);
