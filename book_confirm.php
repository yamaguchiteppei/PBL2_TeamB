<?php
session_start();

// ===== 1. 入力データの取得（前の画面から） =====
$bookName   = htmlspecialchars($_POST['book_name'] ?? '', ENT_QUOTES, 'UTF-8');
$tradeType  = $_POST['trade'] ?? 'free';
$price      = htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES, 'UTF-8');
$faculty    = htmlspecialchars($_POST['faculty'] ?? '', ENT_QUOTES, 'UTF-8');

// ★追加：学科・コース・詳細情報を受け取る
$department = htmlspecialchars($_POST['department'] ?? '', ENT_QUOTES, 'UTF-8');
$course     = htmlspecialchars($_POST['course'] ?? '', ENT_QUOTES, 'UTF-8');
$bookDetail = htmlspecialchars($_POST['book_detail'] ?? '', ENT_QUOTES, 'UTF-8');

$imagePath  = $_POST['book_image_path'] ?? '';

// ===== 2. 画像アップロード処理（初回アクセス時） =====
// book_upload.php からファイルが送信された場合
if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === UPLOAD_ERR_OK) {
    // 保存先を 'images' フォルダに統一
    $uploadDir = __DIR__ . '/images/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
    
    // ファイル名が被らないように現在時刻を付与
    $fileName = date("YmdHis") . '_' . basename($_FILES['book_image']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['book_image']['tmp_name'], $targetPath)) {
        $imagePath = 'images/' . $fileName;
    } else {
        $imagePath = 'images/sample_book.jpg'; // エラー時のダミー
    }
} elseif (!$imagePath) {
    // 画像がない、または確認画面のリロード時でパスがない場合
    $imagePath = 'images/sample_book.jpg';
}

// ===== 3. 出品確定ボタンが押された時の処理 =====
if (isset($_POST['confirm'])) {

    $books_file = __DIR__ . '/books.json';
    $books = file_exists($books_file)
        ? json_decode(file_get_contents($books_file), true)
        : [];

    $seller = $_SESSION['user']['username'] ?? 'ゲストユーザー';

    /* ===== 同名チェック ===== */
    $existing_titles = [];

    foreach ($books as $b) {
        if (
            ($b['seller'] ?? '') === $seller &&
            (
                ($b['title'] ?? '') === $bookName ||
                preg_match('/^' . preg_quote($bookName, '/') . '\(\d+\)$/', $b['title'] ?? '')
            )
        ) {
            $existing_titles[] = $b['title'];
        }
    }

    /* ===== 別名付与 ===== */
    if (!empty($existing_titles)) {
        $i = 2;
        do {
            $newName = $bookName . "({$i})";
            $i++;
        } while (in_array($newName, $existing_titles, true));

        $bookName = $newName;
    }

    $profileFile = __DIR__ . '/data/profiles/' . ($_SESSION['user']['username'] ?? '') . '.json';
    $profile = file_exists($profileFile) ? json_decode(file_get_contents($profileFile), true): [];
    $sellerName = $profile['display_name'] ?? ($_SESSION['user']['username'] ?? '名無し');
    $books_file = __DIR__ . '/books.json';

    // ログインユーザー名（未ログイン時はゲスト扱い）
    $seller = $_SESSION['user']['username'] ?? 'ゲストユーザー';

    // 新しい書籍データ（★ここに学科などを追加）
    $newBook = [
        'id'          => uniqid(), // 一意のIDをつけておくと便利
        'title'       => $bookName ?: '未入力のタイトル',
        'image'       => $imagePath,
        'trade_type'  => $tradeType, // free or paid
        'price'       => ($tradeType === 'paid' && $price) ? (int)$price : 0,
        'faculty'     => $faculty ?: '未選択',
        'department'  => $department, // ★追加
        'course'      => $course,     // ★追加
        'detail'      => $bookDetail, // ★追加
        'seller'      => $seller,
        'created_at'  => date('Y-m-d H:i:s'),
        'sellerName'  => $sellerName
    ];

    // JSONへ追加して保存
    $books[] = $newBook;
    file_put_contents($books_file, json_encode($books, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    // 登録後に購入画面（リスト）へリダイレクト
    header("Location: book_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>出品内容の確認 - yuzurin</title>
<link rel="stylesheet" href="style/book_confirm.css">
<style>
    .confirm-box { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
    .confirm-item { margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    .confirm-item label { font-weight: bold; display: block; color: #555; }
    .confirm-item img { max-width: 200px; border-radius: 5px; margin-top: 5px; }
    .buttons { text-align: center; margin-top: 20px; }
    .submit-btn { background-color: #ff9900; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 1em; cursor: pointer; }
    .back-btn { background-color: #ccc; color: black; padding: 10px 20px; border: none; border-radius: 5px; margin-right: 10px; cursor: pointer; }
</style>
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
    <p class="message">以下の内容で出品します。よろしければ「出品する」を押してください。</p>

    <div class="confirm-item">
        <label>・教科書名</label>
        <span><?= $bookName ?: '（未入力）' ?></span>
    </div>

    <div class="confirm-item">
        <label>・教科書画像</label>
        <img src="<?= htmlspecialchars($imagePath) ?>" alt="教科書画像">
    </div>

    <div class="confirm-item">
        <label>・譲渡形式</label>
        <span><?= ($tradeType === 'paid') ? "有償取引（{$price}円）" : "無償提供（OK!）" ?></span>
    </div>

    <div class="confirm-item">
        <label>・使用学部 / 学科 / コース</label>
        <span>
            <?= $faculty ?: '（未選択）' ?><br>
            <?= $department ? " / {$department}" : "" ?><br>
            <?= $course ? " / {$course}" : "" ?>
        </span>
    </div>

    <div class="confirm-item">
        <label>・詳細情報</label>
        <span><?= nl2br($bookDetail) ?></span>
    </div>

    <div class="confirm-item">
        <label>・出品者</label>
        <span><?= htmlspecialchars($_SESSION['user']['username'] ?? 'ゲストユーザー') ?></span>
    </div>

    <div class="buttons">
        <button class="back-btn" onclick="history.back()">修正する</button>
        
        <form method="post" style="display:inline;">
            <input type="hidden" name="book_name" value="<?= $bookName ?>">
            <input type="hidden" name="trade" value="<?= $tradeType ?>">
            <input type="hidden" name="price" value="<?= $price ?>">
            <input type="hidden" name="faculty" value="<?= $faculty ?>">
            
            <input type="hidden" name="department" value="<?= $department ?>">
            <input type="hidden" name="course" value="<?= $course ?>">
            <input type="hidden" name="book_detail" value="<?= $bookDetail ?>">

            <input type="hidden" name="book_image_path" value="<?= $imagePath ?>">
            
            <button class="submit-btn" type="submit" name="confirm">出品する</button>
        </form>
    </div>
</div>

</body>
</html>
