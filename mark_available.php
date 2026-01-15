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
$updated = false;
foreach ($books as &$b) {
    if (
        ($b['seller'] ?? '') === $seller &&
        ($b['title'] ?? '') === $book
    ) {
        $b['status'] = 'active';
        $updated = true;
        break;
    }
}

file_put_contents(
    $file,
    json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $updated,
        'seller' => $seller,
        'book' => $book,
        'status' => $updated ? 'active' : null,
    ]);
    exit;
}

/* チャット画面に戻す */
header("Location: message_list.php?seller=" . urlencode($seller) . "&book=" . urlencode($book));
exit;
