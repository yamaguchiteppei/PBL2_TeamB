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
    $book_map = [];
foreach ($books as $b) {
    $book_map[$b['title']] = $b;
}
}

/* ===== GET パラメータ ===== */
$seller = $_GET['seller'] ?? '';
$buyer  = $_SESSION['user']['username'];
$book   = $_GET['book'] ?? '';
$selected_key = $_GET['chat_key'] ?? '';

if ($selected_key && isset($chat_data[$selected_key])) {
    [$u1, $u2, $book] = explode('_', $selected_key, 3);

    $current = $_SESSION['user']['username'];
    $seller = $u1; // 仮
    $buyer  = $u2;

    // books.json から本当の seller を確定
    foreach ($books as $b) {
        if ($b['title'] === $book && ($b['seller'] === $u1 || $b['seller'] === $u2)) {
            $seller = $b['seller'];
            $buyer  = ($seller === $u1) ? $u2 : $u1;
            break;
        }
    }
}




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
$seller_chats = []; // 自分が出品者
$buyer_chats  = []; // 自分が購入者
$current = $_SESSION['user']['username'];

foreach ($chat_data as $key => $messages) {
    $parts = explode('_', $key, 3);
    if (count($parts) !== 3) {
        continue; // 旧仕様は無視
    }

    [$s_name, $b_name, $s_book] = $parts;

    $real_seller = '';
foreach ($books as $bk) {
    if ($bk['title'] === $s_book &&
        ($bk['seller'] === $s_name || $bk['seller'] === $b_name)) {
        $real_seller = $bk['seller'];
        break;
    }
}

if ($real_seller === '') continue;

$real_buyer = ($real_seller === $s_name) ? $b_name : $s_name;

        [$u1, $u2] = [$s_name, $b_name];
    $partner = ($current === $u1) ? $u2 : $u1;

    // ★ 1対1保証（これ1回だけ）
    if ($current !== $s_name && $current !== $b_name) {
        continue;
    }

    $last_msg = end($messages);
    // 自分が seller または buyer のチャットだけ表示


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
    $partner = ($current === $s_name) ? $b_name : $s_name;
    $display_name = $partner;
    $profile_file = __DIR__ . "/data/profiles/{$partner}.json";
    if (file_exists($profile_file)) {
        $profile_data = json_decode(file_get_contents($profile_file), true);
        if (!empty($profile_data['display_name'])) {
            $display_name = $profile_data['display_name'];
        }
    }

    /* アバター */
/* アバター（profiles の JSON を参照） */
$avatar_path = 'images/default.jpg'; // デフォルト

$profile_file = __DIR__ . "/data/profiles/{$partner}.json";
if (file_exists($profile_file)) {
    $profile_data = json_decode(file_get_contents($profile_file), true);

    if (
        !empty($profile_data['avatar']) &&
        file_exists(__DIR__ . '/' . $profile_data['avatar'])
    ) {
        $avatar_path = $profile_data['avatar'];
    }
}


/* 売却済み判定（book 基準・全チャット共通） */
$is_sold_chat = false;

if (isset($book_map[$s_book])) {
    $is_sold_chat = (($book_map[$s_book]['status'] ?? 'active') === 'sold');
}
    $book_index = null;

$selected_book_index = null;

if ($selected_key && isset($chat_data[$selected_key])) {
    foreach ($books as $i => $b) {
        if (
            ($b['seller'] ?? '') === $seller &&
            ($b['title'] ?? '') === $book
        ) {
            $selected_book_index = $i;
            break;
        }
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

$chat_item = [
    'seller'       => $real_seller,
    'buyer'        => $real_buyer,
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

// ★ 自分がどちらの立場かで振り分け
if ($real_seller === $current) {
    $seller_chats[] = $chat_item; // 自分が出品者
} else {
    $buyer_chats[] = $chat_item;  // 自分が購入者
}

}

/* ===== 最新順 ===== */
usort($seller_chats, fn($a, $b) => strcmp($b['time'], $a['time']));
usort($buyer_chats, fn($a, $b) => strcmp($b['time'], $a['time']));
$messages = [];

if ($selected_key && isset($chat_data[$selected_key])) {
    $messages = $chat_data[$selected_key];
}
/* ===== 右カラム用：自分・相手のアバター ===== */
$current = $_SESSION['user']['username'];

/* 相手を確定（seller / buyer どちらか） */
$partner = ($current === $seller) ? $buyer : $seller;

/* デフォルト */
$my_avatar = 'images/default.jpg';
$partner_avatar = 'images/default.jpg';

/* 自分のアバター */
$my_profile = __DIR__ . "/data/profiles/{$current}.json";
if (file_exists($my_profile)) {
    $p = json_decode(file_get_contents($my_profile), true);
    if (!empty($p['avatar']) && file_exists(__DIR__ . '/' . $p['avatar'])) {
        $my_avatar = $p['avatar'];
    }
}

/* 相手のアバター（← 左カラムと完全一致） */
$partner_profile = __DIR__ . "/data/profiles/{$partner}.json";
if (file_exists($partner_profile)) {
    $p = json_decode(file_get_contents($partner_profile), true);
    if (!empty($p['avatar']) && file_exists(__DIR__ . '/' . $p['avatar'])) {
        $partner_avatar = $p['avatar'];
    }
}

// ===== 右カラム用：表示する相手情報 =====
if ($current === $seller) {
    // 自分が出品者 → 相手は購入希望者
    $partner_user = $buyer;
    $partner_label = '購入希望者';
} else {
    // 自分が購入者 → 相手は出品者
    $partner_user = $seller;
    $partner_label = '出品者';
}

// 相手の表示名
$partner_display_name = $partner_user;
$partner_profile = __DIR__ . "/data/profiles/{$partner_user}.json";
if (file_exists($partner_profile)) {
    $p = json_decode(file_get_contents($partner_profile), true);
    if (!empty($p['display_name'])) {
        $partner_display_name = $p['display_name'];
    }
}


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

<!-- ================= 出品中の教科書 ================= -->

<h4 class="chat-group-title toggle" data-target="seller">
  <span class="toggle-icon">▼</span>
  🟦 出品中の教科書
</h4>

<input
  type="text"
  class="chat-search"
  placeholder="教科書名・出品者名で検索"
  data-target="seller"
/>

<div class="chat-group seller-group">
  <?php if (empty($seller_chats)): ?>
    <p class="no-chat small">出品中のチャットはありません</p>
  <?php else: ?>
    <?php foreach ($seller_chats as $chat): ?>
      <?php include 'chat_item.php'; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- ================= 購入希望の教科書 ================= -->

<h4 class="chat-group-title toggle" data-target="buyer">
  <span class="toggle-icon">▼</span>
  🟩 購入希望の教科書
</h4>

<input
  type="text"
  class="chat-search"
  placeholder="教科書名・出品者名で検索"
  data-target="buyer"
/>

<div class="chat-group buyer-group">
  <?php if (empty($buyer_chats)): ?>
    <p class="no-chat small">購入中のチャットはありません</p>
  <?php else: ?>
    <?php foreach ($buyer_chats as $chat): ?>
      <?php include 'chat_item.php'; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</div>

    <!-- 右カラム -->
    <div class="chat-screen">
        <?php if (!$selected_key || !isset($chat_data[$selected_key])): ?>
            <div class="no-selection">
                <p>👈 左の一覧から教科書を選択してください。</p>
            </div>
        <?php else: ?>

    <div class="chat-header"
    data-chat-key="<?= htmlspecialchars($selected_key) ?>"
     data-seller="<?= htmlspecialchars($seller, ENT_QUOTES) ?>"
     data-buyer="<?= htmlspecialchars($buyer, ENT_QUOTES) ?>"
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
  <?= $partner_label ?>名:
  <a href="view_profile.php?user=<?= urlencode($partner_user) ?>"
     class="seller-profile-link">
    <?= htmlspecialchars($partner_display_name) ?>
  </a>
</span>

<span class="seller-account">
（ID:<?= htmlspecialchars($partner_user) ?>）
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
    <input type="hidden" name="index" value="<?= (int)$selected_book_index ?>">
    <input type="hidden" name="redirect"
           value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
    <button class="header-action-btn available">
        🔄 販売中に戻す
    </button>
</form>
            <?php else: ?>
                <!-- 売却済みにする -->
<form action="mark_sold.php" method="post"
      onsubmit="return confirm('【注意】教科書を購入者に渡してから、売却済みにしてください。\nこの教科書を売却済みにしますか？');">
    <input type="hidden" name="index" value="<?= (int)$selected_book_index ?>">
    <input type="hidden" name="redirect"
           value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
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



            <div class="chat-messages" id="chatMessages">
                    <?php if (empty($messages)): ?>
        <p class="no-message">まだメッセージはありません。</p>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
            <?php
$sender = $msg['sender'];
$sender_avatar = 'images/default.jpg';

$profile_file = __DIR__ . "/data/profiles/{$sender}.json";
if (file_exists($profile_file)) {
    $profile_data = json_decode(file_get_contents($profile_file), true);
    if (
        !empty($profile_data['avatar']) &&
        file_exists(__DIR__ . '/' . $profile_data['avatar'])
    ) {
        $sender_avatar = $profile_data['avatar'];
    }
}
?>
<div class="chat-message <?= $msg['sender'] === $current ? 'me' : 'other' ?>">

    <?php if ($msg['sender'] !== $current): ?>
        <img src="<?= htmlspecialchars($partner_avatar) ?>"
             class="message-avatar"
             alt="avatar">
    <?php endif; ?>

    <div class="message-bubble">
        <div class="message-text">
            <?= htmlspecialchars($msg['text']) ?>
        </div>
        <div class="message-time">
            <?= htmlspecialchars($msg['time']) ?>
        </div>
    </div>

    <?php if ($msg['sender'] === $current): ?>
        <img src="<?= htmlspecialchars($my_avatar) ?>"
             class="message-avatar"
             alt="avatar">
    <?php endif; ?>

</div>

        <?php endforeach; ?>
    <?php endif; ?>
            </div>

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
