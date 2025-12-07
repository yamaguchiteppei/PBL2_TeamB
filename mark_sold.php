<?php
session_start();

$index = intval($_POST['index']);

// 読み込み
$file = __DIR__ . '/books.json';
$books = json_decode(file_get_contents($file), true);

// ステータス変更
$books[$index]['status'] = 'sold';

// 保存
file_put_contents($file, json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// 完了後 book_list へ
header("Location: book_list.php");
exit;
