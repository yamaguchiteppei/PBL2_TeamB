<?php 
// ====== データ読み込み ======
$books_file = __DIR__ . '/books.json'; 
$books = file_exists($books_file) ? json_decode(file_get_contents($books_file), true) : [];

$index = $_GET['index'] ?? null;
$book = $books[$index] ?? null;

if (!$book) {
    die("対象の教科書が見つかりません。");
}

// ====== 更新処理 ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $books[$index]['title'] = $_POST['title'];
    $books[$index]['price'] = $_POST['price'];
    $books[$index]['faculty'] = $_POST['faculty'];
    file_put_contents($books_file, json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: book_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>教科書編集</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style/book_edit.css">
<script src="script/book_edit.js" defer></script>
</head>
<body>

<!-- ===== ナビゲーション ===== -->
<header>
  <nav class="menu">
    <button onclick="location.href='book_list.php'">購入画面</button>
    <button onclick="location.href='book_upload.php'">出品</button>
    <button onclick="location.href='message_list.php'">メッセージ</button>
    <button onclick="location.href='login.php'">ログイン</button>
    <button onclick="location.href='profile.php'">プロフィール</button>
  </nav>
</header>

<!-- ===== タイトル＋戻るボタン ===== -->
<div class="title-bar">
  <a href="book_list.php" class="back-btn">
    <span>←</span> 教科書一覧へ戻る
  </a>
  <h2>📘 教科書情報の編集</h2>
</div>

<!-- ===== 編集フォーム ===== -->
<form method="post">
    <label>教科書名</label>
    <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>

    <label>価格（円）</label>
    <input type="number" name="price" value="<?= htmlspecialchars($book['price']) ?>" min="0" step="100">

    <label>学部・学科</label>
    <select name="faculty">
        <option <?= $book['faculty']==='共通教育'?'selected':'' ?>>共通教育</option>
        <option <?= $book['faculty']==='工学部　応用情報コース'?'selected':'' ?>>工学部　応用情報コース</option>
        <option <?= $book['faculty']==='工学部　コンピュータ科学コース'?'selected':'' ?>>工学部　コンピュータ科学コース</option>
        <option <?= $book['faculty']==='教育学部'?'selected':'' ?>>教育学部</option>
        <option <?= $book['faculty']==='法文学部'?'selected':'' ?>>法文学部</option>
    </select>

    <button type="submit">更新する</button>
</form>

</body>
</html>
