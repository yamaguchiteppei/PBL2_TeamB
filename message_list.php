<?php
// ==== チャットログ読み込み ====
$chat_file = __DIR__ . '/chat_log.json';
if (!file_exists($chat_file)) file_put_contents($chat_file, json_encode([], JSON_UNESCAPED_UNICODE));
$chat_data = json_decode(file_get_contents($chat_file), true) ?? [];

// ==== 書籍一覧読み込み (左カラム用に各チャットの売却状態を判定) ====
$books = [];
$books_file = __DIR__ . '/books.json';
if (file_exists($books_file)) {
  $books = json_decode(file_get_contents($books_file), true) ?? [];
}

// ==== GETパラメータ ====
$seller = $_GET['seller'] ?? '';
$book   = $_GET['book'] ?? '';
$selected_key = $seller && $book ? "{$seller}_{$book}" : '';

// ==== 書籍の売却状態チェック ====
$is_sold = false;
$books_file = __DIR__ . '/books.json';
if (file_exists($books_file) && $seller && $book) {
  $books = json_decode(file_get_contents($books_file), true) ?? [];
  foreach ($books as $b) {
    if ((($b['seller'] ?? '') === $seller) && (($b['title'] ?? '') === $book) && isset($b['status']) && $b['status'] === 'sold') {
      $is_sold = true;
      break;
    }
  }
}

// チャットヘッダーに表示する教科書画像を決定
$book_image = '';
if ($seller && $book && file_exists($books_file)) {
  foreach ($books as $b) {
    if ((($b['seller'] ?? '') === $seller) && (($b['title'] ?? '') === $book)) {
      $book_image = $b['image'] ?? '';
      break;
    }
  }
}
if ($book_image && !file_exists(__DIR__ . '/' . $book_image)) $book_image = '';
if (empty($book_image)) $book_image = 'images/sample_book.png';

// ==== 取引中ユーザー一覧 ====
$chats = [];
$selected_chat = null; // 選択されたチャット情報を保持
foreach ($chat_data as $key => $messages) {
    [$s_name, $s_book] = explode('_', $key, 2);
    $last_msg = end($messages);
    $unread = 0;
    $current = $_SESSION['user']['username'];
    foreach ($messages as $msg) {
        if ($msg['sender'] !== $current && (empty($msg['read']) || $msg['read'] === false)) {
            $unread++;
        }
    }

    // プロフィール名とアバター
    $profile_file = __DIR__ . "/data/profiles/{$s_name}.json";
    $display_name = $s_name;
    if (file_exists($profile_file)) {
        $profile_data = json_decode(file_get_contents($profile_file), true);
        if (!empty($profile_data['display_name'])) {
            $display_name = $profile_data['display_name'];
        }
    }
    // プロフィール JSON に avatar フィールドがあればそちらを優先
    $base = "uploads/avatars/avatar_" . preg_replace('/[^a-zA-Z0-9]/', '', $s_name);

// png / jpg / jpeg を順番にチェック
$try_ext = ['png', 'jpg', 'jpeg'];
$avatar_path = "images/default.jpg"; // デフォルト

foreach ($try_ext as $ext) {
    if (file_exists(__DIR__ . "/{$base}.{$ext}")) {
        $avatar_path = "{$base}.{$ext}";
        break;
    }
}

    if (!empty($profile_data['avatar'])) {
      $candidate = $profile_data['avatar'];
      if (file_exists(__DIR__ . '/' . $candidate)) {
        $avatar_path = $candidate;
      }
    }
    if (!file_exists(__DIR__ . '/' . $avatar_path)) $avatar_path = "images/default.jpg";

    // このチャット対象の教科書が売却済みか判定
    $is_sold_chat = false;
    foreach ($books as $b) {
      if ((($b['seller'] ?? '') === $s_name) && (($b['title'] ?? '') === $s_book) && isset($b['status']) && $b['status'] === 'sold') {
        $is_sold_chat = true;
        break;
      }
    }

    // このチャット対象の教科書画像を取得
$book_image_chat = '';
foreach ($books as $b) {
    if ((($b['seller'] ?? '') === $s_name) && (($b['title'] ?? '') === $s_book)) {
        $book_image_chat = $b['image'] ?? '';
        break;
    }
}
// 画像が存在しないときはサンプル画像
if ($book_image_chat && !file_exists(__DIR__ . '/' . $book_image_chat)) {
    $book_image_chat = 'images/sample_book.png';
}
if (empty($book_image_chat)) {
    $book_image_chat = 'images/sample_book.png';
}

    $chat_info = [
        'seller' => $s_name,
        'book' => $s_book,
        'avatar' => $avatar_path,
        'display_name' => $display_name,
        'last_msg' => $last_msg['text'] ?? '',
        'time' => $last_msg['time'] ?? '',
        'unread' => $unread,
        'key' => $key,
        'is_sold' => $is_sold_chat,
        'book_image' => $book_image_chat 
    ];
    
    $chats[] = $chat_info;
    
    // 選択されたチャットの情報を保存
    if ($key === $selected_key) {
        $selected_chat = $chat_info;
    }
}

usort($chats, fn($a, $b) => strcmp($b['time'], $a['time']));

// 新規チャットの場合、選択された教科書をリストに追加
if ($seller && $book && !$selected_chat) {
    $profile_file = __DIR__ . "/data/profiles/{$seller}.json";
    $display_name = $seller;
    if (file_exists($profile_file)) {
        $profile_data = json_decode(file_get_contents($profile_file), true);
        if (!empty($profile_data['display_name'])) {
            $display_name = $profile_data['display_name'];
        }
    }
    $avatar_path = "uploads/avatars/avatar_" . preg_replace('/[^a-zA-Z0-9]/', '', $seller) . ".png";
    if (!file_exists($avatar_path)) $avatar_path = "images/sample_avatar.png";
    
    $new_chat = [
        'seller' => $seller,
        'book' => $book,
        'avatar' => $avatar_path,
        'display_name' => $display_name,
        'last_msg' => '',
        'time' => date('Y-m-d H:i:s'),
        'unread' => 0,
        'key' => $selected_key
    ];
    // 先頭に追加
    array_unshift($chats, $new_chat);
    $selected_chat = $new_chat;
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
    const CURRENT_USER = "<?= $_SESSION['user']['username'] ?>";
</script>

<script src="script/message_list.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
  <div class="chat-list" id="chatList">
    <h3>📚 取引中の教科書</h3>
    <?php if (empty($chats)): ?>
      <p class="no-chat">取引中のユーザーはまだいません。</p>
    <?php else: ?>
      <?php foreach ($chats as $chat): ?>
        <div class="chat-item <?= $chat['key'] === $selected_key ? 'active' : '' ?>"
             data-seller="<?= htmlspecialchars($chat['seller']) ?>"
             data-book="<?= htmlspecialchars($chat['book']) ?>">
          <img src="<?= htmlspecialchars($chat['avatar']) ?>" class="chat-avatar" alt="avatar">
            <div class="chat-info">
            <div class="chat-book"><?= htmlspecialchars($chat['book']) ?></div>
            <div class="chat-seller">
              <div class="display-name"><?= htmlspecialchars($chat['display_name'] ?? $chat['seller']) ?> <?php if (!empty($chat['is_sold'])): ?><span class="sold-badge small">売却済み</span><?php endif; ?></div>
              <div class="account">アカウント: <span class="account-name"><?= htmlspecialchars($chat['seller']) ?></span></div>
              <?php if ($chat['unread'] > 0): ?>
                <span class="unread-badge"><?= $chat['unread'] ?></span>
              <?php endif; ?>
            </div>
            <div class="chat-preview"><?= htmlspecialchars($chat['last_msg'] ?: 'メッセージを開始しましょう') ?></div>
          </div>
         

        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="chat-screen">
    <?php if (!$seller || !$book): ?>
      <div class="no-selection">
        <p>👈 左の一覧から教科書を選択してください。</p>
      </div>
    <?php else: ?>

      <div class="chat-header" data-seller="<?= htmlspecialchars($seller, ENT_QUOTES) ?>" data-book="<?= htmlspecialchars($book, ENT_QUOTES) ?>">
        <h2><?= htmlspecialchars($book) ?> <?php if (!empty($is_sold)): ?><span class="sold-badge">売却済み</span><?php endif; ?></h2>
        <div style="display:flex;align-items:center;gap:8px;">
          <p style="margin:0;"><?= htmlspecialchars($seller) ?></p>
          <button id="reportChatBtn" class="report-btn header">通報</button>
      <?php
      // 新規チャットの場合でもアバターと表示名を取得
      if (!$selected_chat && $seller) {
        $profile_file = __DIR__ . "/data/profiles/{$seller}.json";
        $display_name = $seller;
        if (file_exists($profile_file)) {
          $profile_data = json_decode(file_get_contents($profile_file), true);
          if (!empty($profile_data['display_name'])) {
            $display_name = $profile_data['display_name'];
          }
        }
        $avatar_path = "uploads/avatars/avatar_" . preg_replace('/[^a-zA-Z0-9]/', '', $seller) . ".png";
        if (!file_exists($avatar_path)) $avatar_path = "images/default.jpg";
      } else if ($selected_chat) {
        $display_name = $selected_chat['display_name'];
        $avatar_path = $selected_chat['avatar'];
      } else {
        $display_name = $seller;
        $avatar_path = "images/default.jpg";
      }
      ?>
      <div class="chat-header">
        <div class="chat-header-content">
          <img src="<?= htmlspecialchars($avatar_path) ?>" class="chat-header-avatar" alt="avatar">
          <div class="chat-header-info">
            <h2><?= htmlspecialchars($book) ?></h2>
            <p><?= htmlspecialchars($display_name) ?></p>
          </div>
        </div>
      </div>
      <div class="chat-messages" id="chatMessages"></div>
      <div class="chat-input">
        <input type="text" id="messageInput" placeholder="<?= $is_sold ? 'この教科書は売却済みです' : 'メッセージを入力...' ?>" <?= $is_sold ? 'disabled' : '' ?> >
        <button id="sendBtn" <?= $is_sold ? 'disabled' : '' ?>>送信</button>
      </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
