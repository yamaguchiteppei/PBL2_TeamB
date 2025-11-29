<?php
session_start();

$books_file = __DIR__ . '/books.json';
$books = file_exists($books_file) ? json_decode(file_get_contents($books_file), true) : [];

// ログイン中のユーザー（未ログインはゲスト）
$current_user = $_SESSION['user']['username'] ?? 'ゲストユーザー';

// 教科書を分類
$my_books = [];
$other_books = [];
foreach ($books as $index => $book) {
    $book['index'] = $index;
    if (($book['seller'] ?? '') === $current_user) {
        $my_books[] = $book;
    } else {
        $other_books[] = $book;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>教科書一覧</title>
<link rel="stylesheet" href="style/book_list.css">
<script src="script/book_list.js" defer></script>
<script src="https://kit.fontawesome.com/a4f2e2c2ef.js" crossorigin="anonymous"></script> 
</head>
<body>
<header>
    <nav class="menu">
        <button class="active" onclick="location.href='book_list.php'">購入画面</button>
        <button onclick="location.href='book_upload.php'">出品</button>
        <button onclick="location.href='message_list.php'">メッセージ</button>
        <button onclick="location.href='login.php'">ログイン</button>
        <button onclick="location.href='profile.php'">プロフィール</button>
    </nav>
</header>

<div class="book-container">
    <!-- 左：他者の教科書 -->
    <div class="column" id="colOthers">
        <h2>📘 購入可能な教科書</h2>
        <div class="search-bar">
            <div class="search-input-wrap">
                <input id="searchOthers" type="text" placeholder="教科書名・ 学部・価格で検索..." autocomplete="off" />
                <button id="clearOthers" class="clear-btn" aria-label="clear">✕</button>
            </div>
        </div>

        <p class="nohit hidden" id="nohitOthers">該当する教科書はありません。</p>

        <?php if (empty($other_books)): ?>
            <p style="text-align:center; color:#666;">現在、購入できる教科書 はありません。</p>
        <?php else: ?>
            <?php foreach ($other_books as $book): ?>
                <?php
                  $title = $book['title'] ?? '';
                  $faculty = $book['faculty'] ?? '';
                  $price = $book['price'] ?? '';
                  $seller = $book['seller'] ?? '不明';
                ?>
                <div class="book-item" data-group="others"
                     data-search="<?= htmlspecialchars(mb_strtolower($title.' '.$faculty.' '.$price)) ?>">
                    <img src="<?= htmlspecialchars($book['image']) ?>" class="book-image" alt="book image">
                    <div class="book-info">
                        <div class="book-title"><?= htmlspecialchars($title) ?></div>
                        <div class="book-faculty"><?= htmlspecialchars($faculty) ?></div>
                        <div class="book-price">
                            <?php if ($price === '' || $price === '0'): ?>
                                無料
                            <?php else: ?>
                                <?= htmlspecialchars($price) ?>円
                            <?php endif; ?>
                        </div>
                        <div class="action-buttons">
                            <form action="message_list.php" method="get">
                                <input type="hidden" name="seller" value="<?= htmlspecialchars($seller) ?>">
                                <input type="hidden" name="book" value="<?= htmlspecialchars($title) ?>">
                                <button type="submit" class="message-btn">💬 メッセージ</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 右：自分の出品 -->
    <div class="column" id="colMine">
        <h2>📗 自分の出品教科書</h2>
        <div class="search-bar">
            <div class="search-input-wrap">
                <input id="searchMine" type="text" placeholder="自分の教科書 を検索..." autocomplete="off" />
                <button id="clearMine" class="clear-btn" aria-label="clear">✕</button>
            </div>
        </div>

        <p class="nohit hidden" id="nohitMine">該当する教科書はありません。</p>

        <?php if (empty($my_books)): ?>
            <p style="text-align:center; color:#666;">まだ出品していません。</p>
        <?php else: ?>
            <?php foreach ($my_books as $book): ?>
                <?php
                  $title = $book['title'] ?? '';
                  $faculty = $book['faculty'] ?? '';
                  $price = $book['price'] ?? '';
                  $seller = $book['seller'] ?? $current_user;
                ?>
                <div class="book-item" data-group="mine"
                     data-search="<?= htmlspecialchars(mb_strtolower($title.' '.$faculty.' '.$price)) ?>">
                    <img src="<?= htmlspecialchars($book['image']) ?>" class="book-image" alt="book image">
                    <div class="book-info">
                        <div class="book-title"><?= htmlspecialchars($title) ?></div>
                        <div class="book-faculty"><?= htmlspecialchars($faculty) ?></div>
                        <div class="book-price">
                            <?php if ($price === '' || $price === '0'): ?>
                                無料
                            <?php else: ?>
                                <?= htmlspecialchars($price) ?>円
                            <?php endif; ?>
                        </div>
                        <div class="action-buttons">
                            <form action="book_edit.php" method="get">
                                <input type="hidden" name="index" value="<?= $book['index'] ?>">
                                <button class="edit-btn">✏️ 編集</button>
                            </form>
                            <form action="book_delete.php" method="post" onsubmit="return confirm('本当に削除しますか？');">
                                <input type="hidden" name="index" value="<?= $book['index'] ?>">
                                <button class="delete-btn">🗑 削除</button>
                            </form>
                            <form action="message_list.php" method="get">
                                <input type="hidden" name="seller" value="<?= htmlspecialchars($seller) ?>">
                                <input type="hidden" name="book" value="<?= htmlspecialchars($title) ?>">
                                <button type="submit" class="message-btn">💬 メッセージ</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<footer>©2025 愛媛大学 yuzurinプロジェクト</footer>
</body>
</html>