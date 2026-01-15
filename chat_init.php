<?php
require __DIR__ . '/php/auth.php';
require_login();

/**
 * ファイルパス
 */
$chat_file  = __DIR__ . '/chat_log.json';
$books_file = __DIR__ . '/books.json';

/**
 * ログインユーザー（購入者）
 */
$buyer = $_SESSION['user']['username'] ?? '';
if ($buyer === '') {
    header('Location: login.php');
    exit;
}

/**
 * POST から book のみ受け取る
 * ※ seller は信用しない
 */
$book = $_POST['book'] ?? '';
if ($book === '') {
    header('Location: book_list.php');
    exit;
}

/**
 * books.json から seller を取得
 */
$books = file_exists($books_file)
    ? json_decode(file_get_contents($books_file), true)
    : [];

$seller = '';
foreach ($books as $b) {
    if (($b['title'] ?? '') === $book) {
        $seller = $b['seller'] ?? '';
        break;
    }
}

/**
 * 本が存在しない場合
 */
if ($seller === '') {
    header('Location: book_list.php');
    exit;
}

/**
 * 自分自身とのチャットは禁止
 */
if ($seller === $buyer) {
    header('Location: message_list.php');
    exit;
}

/**
 * chat_log.json 読み込み
 */
$chat_data = file_exists($chat_file)
    ? json_decode(file_get_contents($chat_file), true)
    : [];

/**
 * chat_key 作成（必ず並び替える）
 */
$users = [$seller, $buyer];
sort($users, SORT_STRING);
$chat_key = "{$users[0]}_{$users[1]}_{$book}";

/**
 * チャットがなければ初期化
 */
if (!isset($chat_data[$chat_key])) {
    $chat_data[$chat_key] = [];
    file_put_contents(
        $chat_file,
        json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        LOCK_EX
    );
}

/**
 * セッションに保存（初回自動選択用）
 */
$_SESSION['current_chat_key'] = $chat_key;

/**
 * チャット画面へ
 */
header('Location: message_list.php?chat_key=' . urlencode($chat_key));
exit;
