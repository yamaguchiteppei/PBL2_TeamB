<?php
require __DIR__ . '/php/auth.php';
require_login();

$file = __DIR__ . '/books.json';
$books = json_decode(file_get_contents($file), true) ?? [];

$index = filter_input(INPUT_POST, 'index', FILTER_VALIDATE_INT);
$current = $_SESSION['user']['username'] ?? null;

/* index 不正 */
if ($index === null || $index === false || !isset($books[$index])) {
    header("Location: book_list.php");
    exit;
}

/* 本人チェック（超重要） */
if (($books[$index]['seller'] ?? '') !== $current) {
    header("Location: book_list.php");
    exit;
}

/* 販売中に戻す */
$books[$index]['status'] = 'active';

file_put_contents(
    $file,
    json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

/* AJAX */
if (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'status' => 'active'
    ]);
    exit;
}

/* 通常遷移 */
$redirect = $_POST['redirect']
    ?? $_SERVER['HTTP_REFERER']
    ?? 'book_list.php';

header("Location: " . $redirect);
exit;
