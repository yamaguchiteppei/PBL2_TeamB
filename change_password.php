<?php
session_start();
$data_file = __DIR__ . '/users.json';

// ======== 文字コード設定 ========
mb_language("Japanese");
mb_internal_encoding("UTF-8");

// ======== メール送信処理 ========
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $found = false;
    $users = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];

    foreach ($users as &$user) {
        if ($user['username'] === $username) {
            $found = true;
            $token = bin2hex(random_bytes(16));
            $user['reset_token'] = $token;
            $user['reset_expires'] = time() + 3600; // 1時間有効
            file_put_contents($data_file, json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            // === メール本文 ===
            $domain = $_SERVER['HTTP_HOST'];
            $reset_link = "https://{$domain}/~k111okuy/pbl2/PBL2_TeamB/change_password_confirm.php?token={$token}";
            $email = $user['email'];

            $subject = "【愛媛大学yuzurinプロジェクト】パスワード再設定メール";
            $body = <<<EOT
愛媛大学
学籍番号: {$username} さん

愛媛大学yuzurinプロジェクトのパスワード再設定申請を受け付けました。
以下のURLをクリックして新しいパスワードを設定してください。
※このメールに心当たりがない場合は、リンクを開かないでください。

▼ パスワード再設定URL
{$reset_link}

このリンクは1時間で無効になります。

-----------------------------------
愛媛大学 yuzurinプロジェクト
EOT;

            // === ヘッダー設定 ===
            $from_name = "愛媛大学yuzurinシステム";
            $from_encoded = mb_encode_mimeheader($from_name, "UTF-8");
            $headers = "From: {$from_encoded}<noreply@{$domain}>\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: 8bit\r\n";

            // === メール送信 ===
            if (mb_send_mail($email, $subject, $body, $headers)) {
                $sent = true;
            } else {
                $error = "❌ メール送信に失敗しました。";
            }
            break;
        }
    }
    if (!$found) $error = "該当するユーザーが見つかりません。";
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>パスワード再設定 | 愛媛大学 yuzurin</title>
<link rel="stylesheet" href="style/change_password.css">
<script src="script/change_password.js" defer></script>
<script src="https://kit.fontawesome.com/a4f2e2c2ef.js" crossorigin="anonymous"></script>
</head>
<body>
<header>
    <nav class="menu">
        <button onclick="location.href='book_list.php'">購入画面</button>
        <button onclick="location.href='book_upload.php'">出品</button>
        <button onclick="location.href='message_list.php'">メッセージ</button>
        <button onclick="location.href='login.php'">ログイン</button>
        <button onclick="location.href='profile.php'">プロフィール</button>
    </nav>
</header>

<div class="container">
    <h2><i class="fa-solid fa-key"></i> パスワード再設定</h2>
    <?php if (!empty($sent)): ?>
        <p class="message">📩 メールの送信が完了しました。<br>大学メールを開いて再設定を行ってください。</p>
    <?php elseif (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
        <p>登録済みの学籍番号を入力してください。<br>再設定用リンクを大学メールに送信します。</p>
        <form method="post">
            <input type="text" name="username" placeholder="例：k000000x" required>
            <button type="submit">再設定メールを送信</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
