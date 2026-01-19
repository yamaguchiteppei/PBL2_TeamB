<?php
if (!isset($chat)) return;

$is_active = isset($_GET['chat_key']) && $_GET['chat_key'] === $chat['key'];
$link = 'message_list.php?chat_key=' . urlencode($chat['key']);
?>

<a href="<?= htmlspecialchars($link) ?>"
   class="chat-item <?= $is_active ? 'active' : '' ?> <?= $chat['is_sold'] ? 'sold' : '' ?>"
      data-book="<?= htmlspecialchars(mb_strtolower($chat['book'])) ?>"
   data-seller="<?= htmlspecialchars(mb_strtolower($chat['display_name'])) ?>">

    <!-- 左：相手アバター -->
    <img
        src="<?= htmlspecialchars($chat['avatar']) ?>"
        class="chat-avatar"
        alt="avatar"
    >

    <!-- 中央：テキスト情報 -->
    <div class="chat-item-body">

        <!-- 教科書名 -->
        <div class="chat-item-book">
            <?= htmlspecialchars($chat['book']) ?>
            <?php if ($chat['is_sold']): ?>
                <span class="sold-badge-small">売却済</span>
            <?php endif; ?>
        </div>

        <!-- 出品者名 -->
        <div class="chat-item-seller">
                <?= $chat['seller'] === $_SESSION['user']['username']
        ? '購入希望者：'
        : '出品者：'
    ?>
    <?= htmlspecialchars($chat['display_name']) ?>
        </div>

        <!-- 最新メッセージ -->
        <div class="chat-item-message">
            <?= $chat['last_msg'] !== ''
                ? htmlspecialchars($chat['last_msg'])
                : '（まだメッセージはありません）'
            ?>
        </div>

    </div>

    <!-- 右：教科書画像 -->
    <img
        src="<?= htmlspecialchars($chat['book_image']) ?>"
        class="chat-book-thumb"
        alt="book"
    >

    <!-- 未読数 -->
    <?php if (!empty($chat['unread'])): ?>
        <div class="chat-item-unread">
            <?= (int)$chat['unread'] ?>
        </div>
    <?php endif; ?>

    <!-- 時間（右下） -->
    <?php if (!empty($chat['time'])): ?>
        <div class="chat-item-time">
            <?= htmlspecialchars($chat['time']) ?>
        </div>
    <?php endif; ?>

</a>
