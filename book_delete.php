<?php
$books_file = __DIR__ . '/books.json';
if (!file_exists($books_file)) die("books.json が見つかりません。");

$books = json_decode(file_get_contents($books_file), true);
$index = $_POST['index'] ?? null;

if ($index !== null && isset($books[$index])) {
    unset($books[$index]);
    $books = array_values($books); // インデックスを詰める
    file_put_contents($books_file, json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

header("Location: book_list.php");
exit;
?>