<?php
require __DIR__ . '/php/auth.php';
require_login();

$seller = $_POST['seller'] ?? '';
$book   = $_POST['book'] ?? '';

if ($seller === '' || $book === '') {
    header("Location: message_list.php");
    exit;
}

$file = __DIR__ . '/books.json';
$books = json_decode(file_get_contents($file), true) ?? [];

/* 対象の教科書を探す */
foreach ($books as &$b) {
    if (
        ($b['seller'] ?? '') === $seller &&
        ($b['title'] ?? '') === $book
    ) {
        $b['status'] = 'active';
        break;
    }
}

file_put_contents(
    $file,
    json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

/* チャット画面に戻す */
header("Location: message_list.php?seller=" . urlencode($seller) . "&book=" . urlencode($book));
exit;
