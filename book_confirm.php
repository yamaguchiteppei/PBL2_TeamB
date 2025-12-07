<?php
session_start();

// ===== 入力データの取得 =====
$bookName   = htmlspecialchars($_POST['book_name'] ?? '', ENT_QUOTES, 'UTF-8');
$tradeType  = $_POST['trade'] ?? 'free';
$price      = htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES, 'UTF-8');
$faculty    = htmlspecialchars($_POST['faculty'] ?? '', ENT_QUOTES, 'UTF-8');
$imagePath  = $_POST['book_image_path'] ?? '';

// ===== 画像アップロード処理 =====
if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
    $fileName = time() . '_' . basename($_FILES['book_image']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['book_image']['tmp_name'], $targetPath)) {
        $imagePath = 'uploads/' . $fileName;
    } else {
        $imagePath = 'images/sample_book.jpg';
    }
} elseif (!$imagePath) {
    $imagePath = 'images/sample_book.jpg';
}

// ===== 出品確定時処理 =====
if (isset($_POST['confirm'])) {
    $profileFile = __DIR__ . '/data/profiles/' . ($_SESSION['user']['username'] ?? '') . '.json';
    $profile = file_exists($profileFile) ? json_decode(file_get_contents($profileFile), true): [];
    $sellerName = $profile['display_name'] ?? ($_SESSION['user']['username'] ?? '名無し');
    $books_file = __DIR__ . '/books.json';
    $books = file_exists($books_file) ? json_decode(file_get_contents($books_file), true) : [];

    // ✅ ログインユーザー名（未ログイン時はゲスト扱い）
    $seller = $_SESSION['user']['username'] ?? 'ゲストユーザー';

    // 新しい書籍データ
    $newBook = [
        'title'   => $bookName ?: '未入力のタイトル',
        'image'   => $imagePath,
        'faculty' => $faculty ?: '未選択',
        'price'   => ($tradeType === 'paid' && $price) ? "{$price}" : '0',
        'seller'  => $seller
        'sellerName' => $sellerName
    ];

    // JSONへ追加して保存
    $books[] = $newBook;
    file_put_contents($books_file, json_encode($books, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    // 登録後にリダイレクト
    header("Location: book_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>出品内容の確認</title>
<link rel="stylesheet" href="style/book_confirm.css">
<script src="script/book_confirm.js" defer></script>
<script src="https://kit.fontawesome.com/a4f2e2c2ef.js" crossorigin="anonymous"></script> 
</head>
<body>
<header>
    <nav class="menu">
        <button type="button" onclick="location.href='book_list.php'">購入画面</button>
        <button type="button" class="active">出品</button>
        <button type="button" onclick="location.href='message_list.php'">メッセージ</button>
        <button type="button" onclick="location.href='login.php'">ログイン</button>
        <button type="button" onclick="location.href='profile.php'">プロフィール</button>
    </nav>
</header>

<h2 class="page-title">📘 出品内容の確認</h2>
<div class="confirm-box">
    <p class="message">以下の内容で出品します。内容を確認してください。</p>

    <div class="confirm-item"><label>・教科書名：</label><span><?= $bookName ?: '（未入力）' ?></span></div>
    <div class="confirm-item"><label>・教科書画像：</label><img src="<?= htmlspecialchars($imagePath) ?>" id="bookImage"></div>
    <div class="confirm-item"><label>・譲渡形式：</label><span><?= ($tradeType === 'paid' && $price) ? "有償（{$price}円）" : "無償提供（OK!）" ?></span></div>
    <div class="confirm-item"><label>・使用学部・学科：</label><span><?= $faculty ?: '（未選択）' ?></span></div>
    <div class="confirm-item"><label>・出品者：</label><span><?= htmlspecialchars($_SESSION['user']['username'] ?? '（未ログイン）') ?></span></div>

    <div class="buttons">
        <button class="back-btn" onclick="history.back()">戻る</button>
        <form method="post" style="display:inline;">
            <input type="hidden" name="book_name" value="<?= $bookName ?>">
            <input type="hidden" name="trade" value="<?= $tradeType ?>">
            <input type="hidden" name="price" value="<?= $price ?>">
            <input type="hidden" name="faculty" value="<?= $faculty ?>">
            <input type="hidden" name="book_image_path" value="<?= $imagePath ?>">
            <button class="submit-btn" type="submit" name="confirm">出品する</button>
        </form>
    </div>
</div>
</body>
</html>
