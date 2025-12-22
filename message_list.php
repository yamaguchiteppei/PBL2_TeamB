<?php
require __DIR__ . '/php/auth.php';
require_login();

/* ===== チャットログ読み込み ===== */
$chat_file = __DIR__ . '/chat_log.json';
if (!file_exists($chat_file)) {
    file_put_contents($chat_file, json_encode([], JSON_UNESCAPED_UNICODE));
}
$chat_data = json_decode(file_get_contents($chat_file), true) ?? [];

/* ===== 書籍一覧読み込み ===== */
$books = [];
$books_file = __DIR__ . '/books.json';
if (file_exists($books_file)) {
    $books = json_decode(file_get_contents($books_file), true) ?? [];
}

/* ===== GET パラメータ ===== */
$seller = $_GET['seller'] ?? '';
$book   = $_GET['book'] ?? '';
$selected_key = ($seller && $book) ? "{$seller}_{$book}" : '';

/* ===== 売却状態チェック ===== */
$is_sold = false;
if ($seller && $book && file_exists($books_file)) {
    foreach ($books as $b) {
        if (
            ($b['seller'] ?? '') === $seller &&
            ($b['title'] ?? '') === $book &&
            ($b['status'] ?? '') === 'sold'
        ) {
            $is_sold = true;
            break;
        }
    }
}

/* ===== チャットヘッダー用 教科書画像 ===== */
$book_image = 'images/sample_book.png';
if ($seller && $book) {
    foreach ($books as $b) {
        if (
            ($b['seller'] ?? '') === $seller &&
            ($b['title'] ?? '') === $book
        ) {
            if (!empty($b['image']) && file_exists(__DIR__ . '/' . $b['image'])) {
                $book_image = $b['image'];
            }
            break;
        }
    }
}

/* ===== チャット一覧生成 ===== */
$chats = [];
$current = $_SESSION['user']['username'];

foreach ($chat_data as $key => $messages) {
    [$s_name, $s_book] = explode('_', $key, 2);
    $last_msg = end($messages);

    /* 未読数 */
    $unread = 0;
    foreach ($messages as $msg) {
        if (
            $msg['sender'] !== $current &&
            empty($msg['read'])
        ) {
            $unread++;
        }
    }

    /* 表示名 */
    $display_name = $s_name;
    $profile_file = __DIR__ . "/data/profiles/{$s_name}.json";
    if (file_exists($profile_file)) {
        $profile_data = json_decode(file_get_contents($profile_file), true);
        if (!empty($profile_data['display_name'])) {
            $display_name = $profile_data['display_name'];
        }
    }

    /* アバター */
    $safe_name = preg_replace('/[^a-zA-Z0-9]/', '', $s_name);
    $avatar_path = "images/sample_avatar.png";
    foreach (['png', 'jpg', 'jpeg'] as $ext) {
        $path = "uploads/avatars/avatar_{$safe_name}.{$ext}";
        if (file_exists(__DIR__ . '/' . $path)) {
            $avatar_path = $path;
            break;
        }
    }

    /* 売却済み判定（チャット単位） */
    $is_sold_chat = false;
    foreach ($books as $b) {
        if (
            ($b['seller'] ?? '') === $s_name &&
            ($b['title'] ?? '') === $s_book &&
            ($b['status'] ?? '') === 'sold'
        ) {
            $is_sold_chat = true;
            break;
        }
    }
    $book_index = null;

foreach ($books as $i => $b) {
    if (
        ($b['seller'] ?? '') === $seller &&
        ($b['title'] ?? '') === $book
    ) {
        $book_index = $i;
        break;
    }
}

    /* ===== チャットヘッダー用 追加情報 ===== */
$book_price = '';
$seller_display_name = $seller; // デフォルトはユーザー名

// 価格を取得
foreach ($books as $b) {
    if (
        ($b['seller'] ?? '') === $seller &&
        ($b['title'] ?? '') === $book
    ) {
        $book_price = $b['price'] ?? '';
        break;
    }
}

// 表示名（ニックネーム）を取得
$profile_file = __DIR__ . "/data/profiles/{$seller}.json";
if (file_exists($profile_file)) {
    $profile_data = json_decode(file_get_contents($profile_file), true);
    if (!empty($profile_data['display_name'])) {
        $seller_display_name = $profile_data['display_name'];
    }
}

// 金額表示用
$price_display = ($book_price === '' || $book_price === '0')
    ? '無料'
    : htmlspecialchars($book_price) . '円';

    // 自分の教科書かどうか
$is_my_book = ($seller && $seller === $_SESSION['user']['username']);

    /* 教科書画像（チャット用） */
    $book_image_chat = 'images/sample_book.png';
    foreach ($books as $b) {
        if (
            ($b['seller'] ?? '') === $s_name &&
            ($b['title'] ?? '') === $s_book &&
            !empty($b['image']) &&
            file_exists(__DIR__ . '/' . $b['image'])
        ) {
            $book_image_chat = $b['image'];
            break;
        }
    }

    $chats[] = [
        'seller'       => $s_name,
        'book'         => $s_book,
        'avatar'       => $avatar_path,
        'display_name' => $display_name,
        'last_msg'     => $last_msg['text'] ?? '',
        'time'         => $last_msg['time'] ?? '',
        'unread'       => $unread,
        'key'          => $key,
        'is_sold'      => $is_sold_chat,
        'book_image'   => $book_image_chat,
    ];
}

/* ===== 最新順 ===== */
usort($chats, fn($a, $b) => strcmp($b['time'], $a['time']));
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メッセージ一覧 | yuzurin</title>

    <link rel="stylesheet" href="style/message_list.css">

    <script>
        const CURRENT_USER = "<?= htmlspecialchars($_SESSION['user']['username'], ENT_QUOTES) ?>";
    </script>
    <script src="script/message_list.js"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<header>
    <nav class="menu">
        <button onclick="location.href='book_list.php'">購入画面</button>
        <button onclick="location.href='book_upload.php'">出品</button>
        <button class="active">メッセージ</button>
        <button onclick="location.href='login.php'">ログイン</button>
        <button onclick="location.href='profile.php'">プロフィール</button>
    </nav>
</header>

<div class="message-container">

    <!-- 左カラム -->
    <div class="chat-list" id="chatList">
        <h3>📚 取引中の教科書</h3>

        <?php if (empty($chats)): ?>
            <p class="no-chat">取引中のユーザーはまだいません。</p>
        <?php else: ?>
            <?php foreach ($chats as $chat): ?>
                <div class="chat-item <?= $chat['key'] === $selected_key ? 'active' : '' ?>"
                     data-seller="<?= htmlspecialchars($chat['seller']) ?>"
                     data-book="<?= htmlspecialchars($chat['book']) ?>">

                    <img src="<?= htmlspecialchars($chat['avatar']) ?>"
                         class="chat-avatar"
                         alt="avatar">

                    <div class="chat-info">
                        <div class="chat-book"><?= htmlspecialchars($chat['book']) ?></div>

                        <div class="chat-seller">
                            <div class="display-name">
                                <?= htmlspecialchars($chat['display_name']) ?>
                                <?php if ($chat['is_sold']): ?>
                                    <span class="sold-badge small">売却済み</span>
                                <?php endif; ?>
                            </div>

                            <div class="account">
                                アカウント:
                                <span class="account-name">
                                    <?= htmlspecialchars($chat['seller']) ?>
                                </span>
                            </div>

                            <?php if ($chat['unread'] > 0): ?>
                                <span class="unread-badge"><?= $chat['unread'] ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="chat-preview">
                            <?= htmlspecialchars($chat['last_msg']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 右カラム -->
    <div class="chat-screen">
        <?php if (!$seller || !$book): ?>
            <div class="no-selection">
                <p>👈 左の一覧から教科書を選択してください。</p>
            </div>
        <?php else: ?>

<div class="chat-header"
     data-seller="<?= htmlspecialchars($seller, ENT_QUOTES) ?>"
     data-book="<?= htmlspecialchars($book, ENT_QUOTES) ?>">

    <div class="chat-header-left">
        <h2 class="chat-book-title">
                <a href="book_detail.php?index=<?= urlencode($book_index) ?>"
       class="book-detail-link">
        <?= htmlspecialchars($book) ?>
    </a>
            <?php if ($is_sold): ?>
                <span class="sold-badge">売却済み</span>
            <?php endif; ?>
        </h2>

        <div class="chat-sub-info">
            <span class="seller-display-name">
              出品者名:        <a href="view_profile.php?user=<?= urlencode($seller) ?>"
           class="seller-profile-link">
            <?= htmlspecialchars($seller_display_name) ?>
        </a>
    </span>
            <span class="seller-account">
            （<?= htmlspecialchars($seller) ?>）
            </span>
            <span class="book-price">
                金額:<?= $price_display ?>
            </span>
        </div>
    </div>

    <div class="chat-header-actions">

        <?php if ($is_my_book): ?>
            <?php if ($is_sold): ?>
                <!-- 販売中に戻す -->
                <form action="mark_available.php" method="post"
                      onsubmit="return confirm('販売中に戻しますか？');">
                    <input type="hidden" name="seller" value="<?= htmlspecialchars($seller) ?>">
                    <input type="hidden" name="book" value="<?= htmlspecialchars($book) ?>">
                    <button class="header-action-btn available">
                        🔄 販売中に戻す
                    </button>
                </form>
            <?php else: ?>
                <!-- 売却済みにする -->
                <form action="mark_sold.php" method="post"
                      onsubmit="return confirm('この教科書を売却済みにしますか？');">
                    <input type="hidden" name="seller" value="<?= htmlspecialchars($seller) ?>">
                    <input type="hidden" name="book" value="<?= htmlspecialchars($book) ?>">
                    <button class="header-action-btn sold">
                        ✔ 売却済みにする
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <button id="reportChatBtn" class="header-action-btn report">
            通報
        </button>
    </div>
</div>



            <div class="chat-messages" id="chatMessages"></div>

            <div class="chat-input">
                <input
                    type="text"
                    id="messageInput"
                    placeholder="<?= $is_sold ? 'この教科書は売却済みです' : 'メッセージを入力...' ?>"
                    <?= $is_sold ? 'disabled' : '' ?>
                >
                <button id="sendBtn" <?= $is_sold ? 'disabled' : '' ?>>送信</button>
            </div>

        <?php endif; ?>
    </div>
</div>
</body>
</html>
