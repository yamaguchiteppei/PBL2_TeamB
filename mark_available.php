<?php
session_start();

$index = intval($_POST['index']);

$file = __DIR__ . '/books.json';
$books = json_decode(file_get_contents($file), true);

// ステータス変更
$books[$index]['status'] = 'available';

// 保存
file_put_contents($file, json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'index' => $index, 'status' => 'available']);
    exit;
}

header("Location: book_list.php");
exit;
