<?php
// ==== チャットログ読み込み ====
$chat_file = __DIR__ . '/chat_log.json';
if (!file_exists($chat_file)) file_put_contents($chat_file, json_encode([], JSON_UNESCAPED_UNICODE));
$chat_data = json_decode(file_get_contents($chat_file), true) ?? [];

// ==== GETパラメータ ====
$seller = $_GET['seller'] ?? '';
$book   = $_GET['book'] ?? '';
$selected_key = $seller && $book ? "{$seller}_{$book}" : '';

// ==== 取引中ユーザー一覧 ====
$chats = [];
$selected_chat = null; // 選択されたチャット情報を保持
foreach ($chat_data as $key => $messages) {
    [$s_name, $s_book] = explode('_', $key, 2);
    $last_msg = end($messages);
    $unread = 0;
    foreach ($messages as $msg) {
        if ($msg['sender'] !== 'me' && (empty($msg['read']) || $msg['read'] === false)) {
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
    $avatar_path = "uploads/avatars/avatar_" . preg_replace('/[^a-zA-Z0-9]/', '', $s_name) . ".png";
    if (!file_exists($avatar_path)) $avatar_path = "images/sample_avatar.png";

    $chat_info = [
        'seller' => $s_name,
        'book' => $s_book,
        'avatar' => $avatar_path,
        'display_name' => $display_name,
        'last_msg' => $last_msg['text'] ?? '',
        'time' => $last_msg['time'] ?? '',
        'unread' => $unread,
        'key' => $key
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
<script src="script/message_list.js" defer></script>
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
              <?= htmlspecialchars($chat['display_name'] ?? $chat['seller']) ?>
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
        if (!file_exists($avatar_path)) $avatar_path = "images/sample_avatar.png";
      } else if ($selected_chat) {
        $display_name = $selected_chat['display_name'];
        $avatar_path = $selected_chat['avatar'];
      } else {
        $display_name = $seller;
        $avatar_path = "images/sample_avatar.png";
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
        <input type="text" id="messageInput" placeholder="メッセージを入力...">
        <button id="sendBtn">送信</button>
      </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
