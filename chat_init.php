<?php
require __DIR__ . '/php/auth.php';
require_login();

$chat_file  = __DIR__ . '/chat_log.json';
$books_file = __DIR__ . '/books.json';

// chat_log.json 読み込み
$chat_data = file_exists($chat_file)
    ? json_decode(file_get_contents($chat_file), true)
    : [];

// books.json 読み込み
$books = file_exists($books_file)
    ? json_decode(file_get_contents($books_file), true)
    : [];

// POST パラメータ
$seller = $_POST['seller'] ?? '';
$book   = $_POST['book'] ?? '';
$buyer  = $_SESSION['user']['username'] ?? '';

if ($seller === '' || $book === '' || $buyer === '') {
    header('Location: book_list.php');
    exit;
}

// ------------------------------
// books.json に存在するか確認
// ------------------------------
$book_exists = false;

foreach ($books as $b) {
    if (
        ($b['title'] ?? '') === $book &&
        ($b['seller'] ?? '') === $seller
    ) {
        $book_exists = true;
        break;
    }
}

if (!$book_exists) {
    // 不正 or 既に削除された教科書
    die('この教科書は現在取引できません。');
}

// ------------------------------
// chat_key 作成（順序固定）
// ------------------------------
$users = [$seller, $buyer];
sort($users, SORT_STRING);
$chat_key = $users[0] . '_' . $users[1] . '_' . $book;

// ------------------------------
// chat_data 初期化
// ------------------------------
if (!isset($chat_data[$chat_key])) {
    $chat_data[$chat_key] = [];

    file_put_contents(
        $chat_file,
        json_encode($chat_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        LOCK_EX
    );
}

// セッションに保存（右ペイン表示用）
$_SESSION['current_chat_key'] = $chat_key;

// message_list.php にリダイレクト
header('Location: message_list.php?chat_key=' . urlencode($chat_key));
exit;
