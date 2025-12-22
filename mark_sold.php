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

foreach ($books as &$b) {
    if (
        ($b['seller'] ?? '') === $seller &&
        ($b['title'] ?? '') === $book
    ) {
        $b['status'] = 'sold';
        break;
    }
}

file_put_contents(
    $file,
    json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// AJAXリクエストならJSONで応答、それ以外は従来通りリダイレクト
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'index' => $index, 'status' => 'sold']);
    exit;
}

// 完了後 book_list へ
header("Location: book_list.php");
exit;
