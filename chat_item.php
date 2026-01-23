<?php
if (!isset($chat)) return;

$current = $_SESSION['user']['username'];
$is_active = isset($_GET['chat_key']) && $_GET['chat_key'] === $chat['key'];

// ★ 自分が出品者か？
$is_seller_side = ($chat['seller'] === $current);
?>

<?php if ($is_seller_side): ?>
    <!-- 出品者側：既存チャットを開くだけ -->
    <a href="message_list.php?chat_key=<?= urlencode($chat['key']) ?>"
       class="chat-item <?= $is_active ? 'active' : '' ?> <?= $chat['is_sold'] ? 'sold' : '' ?>"
          data-seller="<?= htmlspecialchars($chat['seller']) ?>"
   data-buyer="<?= htmlspecialchars($chat['buyer']) ?>"
   data-book="<?= htmlspecialchars($chat['book']) ?>">

<?php else: ?>
    <!-- 購入者側：chat_init を通す -->
    <form action="chat_init.php" method="post"
          class="chat-item <?= $is_active ? 'active' : '' ?> <?= $chat['is_sold'] ? 'sold' : '' ?>">

        <input type="hidden" name="seller" value="<?= htmlspecialchars($chat['seller']) ?>">
        <input type="hidden" name="book" value="<?= htmlspecialchars($chat['book']) ?>">

        <button type="submit" class="chat-item-btn">
<?php endif; ?>

    <!-- 共通表示部分 -->
    <img src="<?= htmlspecialchars($chat['avatar']) ?>" class="chat-avatar">

    <div class="chat-item-body">
        <div class="chat-item-book">
            <?= htmlspecialchars($chat['book']) ?>
            <?php if ($chat['is_sold']): ?>
                <span class="sold-badge-small">売却済</span>
            <?php endif; ?>
        </div>

        <div class="chat-item-seller">
            <?= $is_seller_side ? '購入希望者：' : '出品者：' ?>
            <?= htmlspecialchars($chat['display_name']) ?>
        </div>

        <div class="chat-item-message">
            <?= $chat['last_msg'] ?: '（まだメッセージはありません）' ?>
        </div>
    </div>

    <img src="<?= htmlspecialchars($chat['book_image']) ?>" class="chat-book-thumb">

<?php if ($is_seller_side): ?>
    </a>
<?php else: ?>
        </button>
    </form>
<?php endif; ?>
