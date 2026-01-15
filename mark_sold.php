<?php
session_start(); // セッション開始（auth.phpがあるならそれでもOK）

// 1. POSTで送られてきた「本の番号(index)」を受け取る
$index = $_POST['index'] ?? null;

// 2. books.json を読み込む
$file = __DIR__ . '/books.json';
$books = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// 3. データが存在し、指定されたindexがあるかチェック
if ($index !== null && isset($books[$index])) {
    
    // (安全のため) ログイン中のユーザーが出品者本人か確認しても良い
    // $current_user = $_SESSION['user']['username'] ?? '';
    // if (($books[$index]['seller'] ?? '') === $current_user) { ... }

    // 4. ステータスを 'sold' に書き換える
    $books[$index]['status'] = 'sold';

    // 5. 保存する（排他制御は簡易的）
    // JSONファイルは chmod 666 になっている必要があります
    file_put_contents(
        $file,
        json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

// 6. 元の画面に戻る
header("Location: book_list.php");
exit;
?>
