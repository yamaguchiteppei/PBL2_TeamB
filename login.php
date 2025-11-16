<?php
session_start();
$data_file = __DIR__ . '/users.json';

// ====== ログイン処理 ======
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = $username . "@mails.cc.ehime-u.ac.jp";

    if ($username === '' || $password === '') {
        $error = "すべての項目を入力してください。";
    } else {
        $users = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];

        $found = false;
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                if (!$user['verified']) {
                    $error = "まだメール認証が完了していません。メールをご確認ください。";
                    $found = true;
                    break;
                }

                if (password_verify($password, $user['password'])) {
                    $_SESSION['user'] = [
                        'username' => $user['username'],
                        'email' => $user['email']
                    ];
                    header("Location: book_list.php");
                    exit;
                } else {
                    $error = "パスワードが正しくありません。";
                    $found = true;
                    break;
                }
            }
        }
        if (!$found) $error = "ユーザーが見つかりません。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ログイン | 愛媛大学 yuzurin</title>
<link rel="stylesheet" href="style/login.css">
<script src="script/login.js" defer></script>
<script src="https://kit.fontawesome.com/a4f2e2c2ef.js" crossorigin="anonymous"></script>
</head>
<body>
<header>
    <nav class="menu">
        <button onclick="location.href='book_list.php'">購入画面</button>
        <button onclick="location.href='book_upload.php'">出品</button>
        <button onclick="location.href='message_list.php'">メッセージ</button>
        <button class="active">ログイン</button>
        <button onclick="location.href='profile.php'">プロフィール</button>
    </nav>
</header>

<div class="login-container">
    <div class="header">
        <h2><i class="fa-solid fa-right-to-bracket"></i> ログイン</h2>
        <a href="register.php" class="register-link"><i class="fa-solid fa-user-plus"></i> 新規登録へ</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <div class="form-group">
            <label for="username"><i class="fa-solid fa-envelope"></i> 愛媛大学メールアドレス</label>
            <div class="username-row">
                <input type="text" id="username" name="username" required>
                <span class="domain">@mails.cc.ehime-u.ac.jp</span>
            </div>
        </div>

        <div class="form-group password-toggle">
            <label for="password"><i class="fa-solid fa-lock"></i> パスワード</label>
            <input type="password" id="password" name="password" required>
            <span class="toggle-icon" onclick="togglePassword('password', this)">
                <i class="fa-solid fa-eye"></i>
            </span>
        </div>

        <button type="submit"><i class="fa-solid fa-arrow-right-to-bracket"></i> ログイン</button>

        <div class="extra-links">
            <a href="change_password.php"><i class="fa-solid fa-key"></i> パスワードを忘れた方はこちら</a>
        </div>
    </form>
</div>

<footer>© 2025 愛媛大学 yuzurinプロジェクト</footer>
</body>
</html>
