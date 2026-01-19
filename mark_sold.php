<?php
require __DIR__ . '/php/auth.php';
require_login();

$file = __DIR__ . '/books.json';
$books = json_decode(file_get_contents($file), true) ?? [];

$index = filter_input(INPUT_POST, 'index', FILTER_VALIDATE_INT);

if ($index === null || $index === false || !isset($books[$index])) {
    header("Location: book_list.php");
    exit;
}

$books[$index]['status'] = 'sold';

file_put_contents(
    $file,
    json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// AJAX
if (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'status' => 'sold']);
    exit;
}

// 通常遷移
$redirect = $_POST['redirect']
    ?? $_SERVER['HTTP_REFERER']
    ?? 'book_list.php';

header("Location: " . $redirect);
exit;