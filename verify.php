<?php
session_start();

$data_file = __DIR__ . '/users.json'; // register.phpと同じ場所にある

if (!isset($_GET['token']) || trim($_GET['token']) === '') {
    exit("❌ 不正なアクセスです。メールのリンクからアクセスしてください。");
}

$token = trim($_GET['token']);
$users = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];
$found = false;
$already_verified = false;

foreach ($users as &$user) {
    if (isset($user['token']) && $user['token'] === $token) {
        $found = true;

        if (!empty($user['verified']) && $user['verified'] === true) {
            $already_verified = true;
            break;
        }

        $user['verified'] = true;
        file_put_contents($data_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $_SESSION['verified_message'] = "登録が完了しました。ログインしてください。";
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>登録確認</title>
    <style>
        body { font-family: "Noto Sans JP", sans-serif; background-color: #f7f9fc; text-align: center; padding-top: 100px; }
        .verify-box { display: inline-block; background: #fff; padding: 2em 3em; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #004b97; }
        a { display: inline-block; margin-top: 20px; background: #004b97; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; }
        .error { color: #d9534f; font-weight: bold; }
    </style>
</head>
<body>
<div class="verify-box">
<?php if ($found && $already_verified): ?>
    <h2>ℹ️ このアカウントはすでに登録済みです</h2>
    <p>すでにメール認証が完了しています。<br>以下からログインページへ移動してください。</p>
    <a href="login.php">ログインページへ</a>

<?php elseif (!$found): ?>
    <h2 class="error">❌ 無効なリンクです</h2>
    <p>リンクの有効期限が切れているか、すでに使用されています。<br>再度登録を行ってください。</p>
    <a href="register.php">新規登録ページへ</a>
<?php endif; ?>
</div>
</body>
</html>