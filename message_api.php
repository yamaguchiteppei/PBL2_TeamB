<?php
require __DIR__ . '/php/auth.php';
// 簡易デバッグ: 受信内容をログに残す（開発時のみ）
$debug_log = __DIR__ . '/message_api_debug.log';
@file_put_contents($debug_log, "----\n" . date('c') . "\nMETHOD: " . ($_SERVER['REQUEST_METHOD'] ?? '') . "\nUSER: " . json_encode(current_user()) . "\nPOST: " . json_encode($_POST) . "\nGET: " . json_encode($_GET) . "\nCOOKIE: " . json_encode($_COOKIE) . "\n", FILE_APPEND);
// API はログイン済みが前提（通報などの操作で使用）
// API はログイン済みが前提だが、AJAX から呼ばれる可能性があるため
// 未ログイン時はページ遷移（login.php へのリダイレクト）を起こさず
// JSON でエラーを返す。これによりフロントで適切にハンドリングできる。
if (!is_logged_in()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["status" => "error", "msg" => "not_logged_in"]);
    exit;
}

$chat_file = __DIR__ . '/chat_log.json';
if (!file_exists($chat_file)) {
    $r = file_put_contents($chat_file, json_encode([], JSON_UNESCAPED_UNICODE));
    if ($r === false) @file_put_contents($debug_log, "FAILED_CREATE_CHATFILE:\n" . print_r(error_get_last(), true) . "\n", FILE_APPEND);
}
$chat_data = json_decode(file_get_contents($chat_file), true) ?? [];

// ===== メッセージ送信 (POST) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 通報処理か送信か判定
    $action = $_POST['action'] ?? '';
    if ($action === 'report_chat') {

    $seller = trim($_POST['seller'] ?? '');
    $book   = trim($_POST['book'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if ($seller === '' || $book === '') {
        echo json_encode(["status" => "error", "msg" => "invalid chat report"]);
        exit;
    }

    $reports_file = __DIR__ . '/message_reports.json';
    if (!file_exists($reports_file)) {
        file_put_contents($reports_file, json_encode([], JSON_UNESCAPED_UNICODE));
    }

    $reports = json_decode(file_get_contents($reports_file), true) ?? [];

    $reports[] = [
        "type" => "chat_report",
        "seller" => $seller,
        "book" => $book,
        "reason" => $reason ?: "（理由なし）",
        "reporter" => current_user()['username'],
        "reported_at" => date("Y-m-d H:i:s")
    ];

    file_put_contents($reports_file, json_encode($reports, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    echo json_encode(["status" => "ok"]);
    exit;
}

    if ($action === 'report') {
        $seller = trim($_POST['seller'] ?? '');
        $book = trim($_POST['book'] ?? '');
        $text = trim($_POST['text'] ?? '');
        $msg_time = trim($_POST['time'] ?? '');
        $original_sender = trim($_POST['original_sender'] ?? '');
        if ($seller === '' || $book === '' || $text === '') {
            echo json_encode(["status" => "error", "msg" => "invalid input"]);
            exit;
        }
        $reports_file = __DIR__ . '/message_reports.json';
        if (!file_exists($reports_file)) {
            $r = file_put_contents($reports_file, json_encode([], JSON_UNESCAPED_UNICODE));
            if ($r === false) @file_put_contents($debug_log, "FAILED_CREATE_REPORTS:\n" . print_r(error_get_last(), true) . "\n", FILE_APPEND);
        }
        $reports = json_decode(file_get_contents($reports_file), true) ?? [];
        $reports[] = [
            'seller' => $seller,
            'book' => $book,
            'text' => $text,
            'time' => $msg_time,
            'original_sender' => $original_sender,
            'reporter' => current_user(),
            'reported_at' => date("Y-m-d H:i:s")
        ];
        $r = file_put_contents($reports_file, json_encode($reports, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        if ($r === false) @file_put_contents($debug_log, "FAILED_WRITE_REPORTS:\n" . print_r(error_get_last(), true) . "\n", FILE_APPEND);
        echo json_encode(["status" => "ok"]);
        exit;
    }

    // 通常のメッセージ送信
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
        "sender" => current_user()['username'],
        "text"   => $message,
        "time"   => date("Y-m-d H:i:s"),
        "read"   => false
    ];

    $r = file_put_contents($chat_file, json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    if ($r === false) @file_put_contents($debug_log, "FAILED_WRITE_CHAT:\n" . print_r(error_get_last(), true) . "\n", FILE_APPEND);
    echo json_encode(["status" => "ok"]);
    exit;
}

// ===== チャット履歴読み込み =====
if (isset($_GET['load_chat'])) {
    $key = $_GET['load_chat'];
    $current = current_user()['username'];  // ← 今ログインしているユーザー

    $raw = $chat_data[$key] ?? [];
    $result = [];

    foreach ($raw as $m) {
        $sender = $m['sender'] ?? '';
        $result[] = [
            'sender' => $sender,
            'text'   => $m['text'] ?? '',
            'time'   => $m['time'] ?? '',
            'read'   => $m['read'] ?? false,
            // 👇 ここで「自分のメッセージかどうか」をサーバ側でフラグにする
            'is_me'  => ($sender === $current),
        ];
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result);
    exit;
}

// ===== 未読数リスト =====
if (isset($_GET['status']) && $_GET['status'] === 'list') {
    $counts = [];
    foreach ($chat_data as $key => $messages) {
        $unread = 0;
        foreach ($messages as $m) {
            $current = current_user()['username'];
            if ($m['sender'] !== $current && (empty($m['read']) || !$m['read'])) $unread++;
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
            $current = current_user()['username'];
            if ($m['sender'] !== $current) $m['read'] = true;
        }
        unset($m);
        file_put_contents($chat_file, json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    echo json_encode(["status" => "ok"]);
    exit;
}

echo json_encode(["status" => "error", "msg" => "no action"]);
?>