<?php
// change_password_send.php
$data_file = __DIR__ . '/users.json';
$username = trim($_POST['username'] ?? '');
$users = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];

$found = false;

foreach ($users as &$user) {
    if ($user['username'] === $username) {
        $found = true;

        // トークン発行
        $token = bin2hex(random_bytes(16));
        $user['reset_token'] = $token;
        $user['reset_expires'] = time() + 3600; // 1時間有効
        file_put_contents($data_file, json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        // メール用リンク
        $reset_link = "https://sshg.cs.ehime-u.ac.jp/~k484yama/webpro/yuzurin1/change_password_confirm.php?token={$token}";
        $to = $user['email'];
        $subject = "【yuzurin】パスワード再設定リンク";
        $message = "以下のリンクから新しいパスワードを設定してください。\n\n{$reset_link}\n\n※リンクは1時間有効です。";
        $headers = "From: no-reply@yuzurin.local";

        // === 開発時は画面出力 ===
        ?>
        <!DOCTYPE html>
        <html lang="ja">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>パスワード再設定リンク（開発モード）</title>
        <link rel="stylesheet" href="style/change_password_send.css">
        <script src="script/change_password_send.js" defer></script>
        </head>
        <body>
        <div class="container">
            <h2>📩 パスワード再設定リンクを生成しました</h2>
            <p>以下のリンクをクリックすると再設定ページへ移動します。</p>
            <p><a href="<?= htmlspecialchars($reset_link) ?>"><?= htmlspecialchars($reset_link) ?></a></p>
            <p>実際の運用環境ではこのURLが登録メールアドレス宛に送信されます。</p>
            <button onclick="window.location.href='change_password.php'">戻る</button>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// ===== 該当なし =====
if (!$found) {
    header("Location: change_password.php?error=1");
    exit;
}
header("Location: change_password.php?sent=1");
exit;
