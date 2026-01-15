<?php
session_start();

// 1. POSTで送られてきた「本の番号(index)」を受け取る
$index = $_POST['index'] ?? null;

// 2. books.json を読み込む
$file = __DIR__ . '/books.json';
$books = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// 3. データが存在し、指定されたindexがあるかチェック
if ($index !== null && isset($books[$index])) {
    
    // (任意) 出品者本人かどうかのチェックを入れるならここ
    // $current_user = $_SESSION['user']['username'] ?? '';
    // if (($books[$index]['seller'] ?? '') === $current_user) { ... }

    // 4. ステータスを 'active' (販売中) に戻す
    // 'sold' を消す、または 'active' で上書きする
    $books[$index]['status'] = 'active';

    // 5. 保存する
    file_put_contents(
        $file,
        json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

// 6. 一覧画面に戻る
header("Location: book_list.php");
exit;
?>
