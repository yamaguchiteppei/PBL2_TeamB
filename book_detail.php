<?php
session_start();

// books.json ファイルのパス
$books_file = __DIR__ . '/books.json';
// JSONファイルを読み込み、デコードする
$books = file_exists($books_file) ? json_decode(file_get_contents($books_file), true) : [];

// URLから教科書のインデックスを取得
$index = filter_input(INPUT_GET, 'index', FILTER_VALIDATE_INT);
$book = null;

if ($index !== false && $index !== null && isset($books[$index])) {
    $book = $books[$index];
}

// 教科書データが見つからない場合は一覧ページに戻す
if (!$book) {
    header("Location: book_list.php");
    exit;
}

// ログイン中のユーザー（未ログインはゲスト）
$current_user = $_SESSION['user']['username'] ?? 'ゲストユーザー';
$is_my_book = ($book['seller'] ?? '') === $current_user;

// 表示用データの準備
$title = htmlspecialchars($book['title'] ?? 'タイトル不明');
$image = htmlspecialchars($book['image'] ?? 'images/sample_book.jpg');
$faculty = htmlspecialchars($book['faculty'] ?? '学部情報なし');
$price = $book['price'] ?? '0';
$seller = htmlspecialchars($book['seller'] ?? '不明');
// 説明文（description）がない場合は空文字列をデフォルト値とする
$description = htmlspecialchars($book['description'] ?? '特に説明はありません。'); 
$status = $book['status'] ?? 'active';
$is_sold = $status === 'sold';

$price_display = ($price === '0' || $price === '') ? '無償提供（無料）' : "{$price}円";

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $title ?> - 教科書詳細</title>
<link rel="stylesheet" href="style/book_list.css">  
<link rel="stylesheet" href="style/book_detail.css"> 
<script src="https://kit.fontawesome.com/a4f2e2c2ef.js" crossorigin="anonymous"></script>
</head>
<body>
<?php
// ★共通ヘッダー・ナビゲーションのインクルード (include/header_nav.php が必要)
// 参照ファイルが見つからない場合は、この行をコメントアウトし、<header>タグの内容を直接貼り付けてください。
include __DIR__ . '/include/header_nav.php'; 
?>

<div class="detail-container">
    <button class="back-to-list-btn" onclick="location.href='book_list.php'">
        &larr; 一覧に戻る
    </button>
    
    <div class="book-header">
        <img src="<?= $image ?>" class="book-image" alt="<?= $title ?>の画像">
        <div class="book-info">
            <h1><?= $title ?></h1>
            
            <?php if ($is_sold): ?>
                <div class="sold-out-badge">SOLD OUT / 売却済み</div>
            <?php endif; ?>

            <div class="price-tag <?= ($price === '0' || $price === '') ? 'free' : '' ?>">
                価格: **<?= $price_display ?>**
            </div>
            
            <div class="detail-item">
                <strong>出品者:</strong> <?= $seller ?>
            </div>
            <div class="detail-item">
                <strong>使用学部:</strong> <?= $faculty ?>
            </div>
        </div>
    </div>

    <div class="description-section">
        <h2>📗 商品の説明</h2>
        <div class="description-box">
            <?= nl2br($description) ?>
        </div>
    </div>

    <div class="action-area">
        <?php if ($is_my_book): ?>
            <button class="action-btn edit-btn" onclick="location.href='book_edit.php?index=<?= $index ?>'">
                ✏️ 編集する
            </button>
        <?php elseif (!$is_sold): ?>
            <form action="message_list.php" method="get" style="display:inline;">
                <input type="hidden" name="seller" value="<?= $seller ?>">
                <input type="hidden" name="book" value="<?= $title ?>">
                <button type="submit" class="action-btn message-btn">
                    💬 出品者に連絡（購入を相談する）
                </button>
            </form>
        <?php else: ?>
            <p class="sold-message">この教科書は既に売却済みです。</p>
        <?php endif; ?>
    </div>
</div>

<?php
// ★共通フッターのインクルード (include/footer.php が必要)
// 参照ファイルが見つからない場合は、この行をコメントアウトし、<footer>タグの内容を直接貼り付けてください。
include __DIR__ . '/include/footer.php';
?>
</body>
</html>