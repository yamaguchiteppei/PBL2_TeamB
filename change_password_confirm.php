<?php
session_start();
$data_file = __DIR__ . '/users.json';

// ===== トークンの確認 =====
$token = $_GET['token'] ?? '';
$users = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];
$valid_user = null;

// トークンを探す
foreach ($users as &$user) {
    if (!empty($user['reset_token']) && $user['reset_token'] === $token && $user['reset_expires'] > time()) {
        $valid_user = &$user;
        break;
    }
}

// パスワード変更処理
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['new_password']) && $valid_user) {
    $new_pw = $_POST['new_password'];
    $valid_user['password'] = password_hash($new_pw, PASSWORD_DEFAULT);
    unset($valid_user['reset_token'], $valid_user['reset_expires']);
    file_put_contents($data_file, json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>パスワード再設定 | 愛媛大学 yuzurin</title>
<link rel="stylesheet" href="style/change_password_confirm.css">
<script src="script/change_password_confirm.js" defer></script>
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
<?php if (!empty($success)): ?>
    <h2><i class="fa-solid fa-circle-check"></i> パスワード変更完了</h2>
    <p class="success">新しいパスワードが設定されました。</p>
    <p><a href="login.php"><button>ログイン画面へ戻る</button></a></p>

<?php elseif ($valid_user): ?>
    <h2><i class="fa-solid fa-lock"></i> 新しいパスワード設定</h2>
    <p>新しいパスワードを入力してください。</p>
    <form method="post">
        <input type="password" name="new_password" placeholder="新しいパスワード" required>
        <button type="submit">変更を確定</button>
    </form>

<?php else: ?>
    <h2>⚠️ リンクが無効または期限切れです</h2>
    <p class="error">再度「パスワード再設定」ページからやり直してください。</p>
    <p><a href="change_password.php"><button>再設定ページへ</button></a></p>
<?php endif; ?>
</div>
</body>
</html>
