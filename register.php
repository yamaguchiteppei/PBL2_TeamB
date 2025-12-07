<?php
session_start();

$data_file = __DIR__ . '/users.json';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // ====== 入力チェック ======
    if ($username === '' || $password === '' || $password_confirm === '') {
        $error = "すべての項目を入力してください。";
    } elseif ($password !== $password_confirm) {
        $error = "パスワードが一致しません。";
    } elseif (strlen($password) < 8) {
        $error = "パスワードは8文字以上で入力してください。";
    } else {
        $email = $username . "@mails.cc.ehime-u.ac.jp";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(16));

        // ====== JSON読み込み ======
        $users = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];
        foreach ($users as $u) {
            if ($u['email'] === $email) {
                $error = "このユーザーはすでに登録されています。";
                break;
            }
        }

        // ====== 登録処理 ======
        if (empty($error)) {
            $users[] = [
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password,
                'token' => $token,
                'verified' => false
            ];
            file_put_contents($data_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// ====== 認証URL生成（自動判定版） ======

// 1. http or https を自動判定
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

// 2. ホスト名（localhost / example.com）
$host = $_SERVER['HTTP_HOST'];

// 3. スクリプトのディレクトリパスを取得（例：/yuzurin2）
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// 4. フルURLを生成（フォルダ構造も反映）
$baseUrl = $scheme . "://" . $host . $scriptDir;

// 5. verify.php のリンクを作成
$verify_link = $baseUrl . "/php/verify.php?token={$token}";
            // ====== メール送信処理（日本語UTF-8固定） ======
            mb_language("Japanese");
            mb_internal_encoding("UTF-8");

            $subject = "【愛媛大学yuzurinプロジェクト】登録確認メール";
            $body = <<<EOT
愛媛大学
学籍番号: {$username} さん

愛媛大学yuzurinプロジェクトシステムへの仮登録ありがとうございます。
以下のURLをクリックして本登録を完了してください。
※このメールに心当たりがない場合は、このメールを無視してください。

▼ 登録確認URL
{$verify_link}
EOT;

            $from_name = "愛媛大学yuzurinシステム";
            $from_encoded = mb_encode_mimeheader($from_name, "UTF-8", "B");

            $headers = "From: {$from_encoded} <noreply@{$domain}>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: 8bit\r\n";

            if (mb_send_mail($email, $subject, $body, $headers)) {
                $success = "✅ 確認メールを送信しました：<b>{$email}</b><br>メールから本登録を完了してください。";
            } else {
                $error = "❌ メール送信に失敗しました。サーバー設定を確認してください。";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>新規登録 | 愛媛大学 yuzurin</title>
<link rel="stylesheet" href="style/register.css">
<script src="script/register.js" defer></script>
<script src="https://kit.fontawesome.com/a4f2e2c2ef.js" crossorigin="anonymous"></script>
</head>
<body>

<header>
  <nav class="menu">
    <button onclick="location.href='book_list.php'">購入画面</button>
    <button onclick="location.href='book_upload.php'">出品</button>
    <button onclick="location.href='message_list.php'">メッセージ</button>
    <button class="active">新規登録</button>
    <button onclick="location.href='profile.php'">プロフィール</button>
  </nav>
</header>

<div class="register-card">
  <div class="register-header">
    <button class="back-btn" onclick="history.back()"><i class="fa-solid fa-arrow-left"></i> 戻る</button>
    <h2><i class="fa-solid fa-user-plus"></i> 新規登録</h2>
  </div>

  <?php if (!empty($error)): ?>
    <div class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php elseif (!empty($success)): ?>
    <div class="success-message"><?= $success ?></div>
  <?php endif; ?>

  <form action="register.php" method="post" onsubmit="return checkPasswords()">
    <div class="form-group">
      <label for="username"><i class="fa-solid fa-user"></i> 愛媛大学ユーザー名</label>
      <input type="text" id="username" name="username" required>
      <span class="domain">@mails.cc.ehime-u.ac.jp</span>
    </div>

    <div class="form-group">
      <label for="password"><i class="fa-solid fa-lock"></i> パスワード（8文字以上）</label>
      <input type="password" id="password" name="password" required>
    </div>

    <div class="form-group">
      <label for="password_confirm"><i class="fa-solid fa-lock"></i> パスワード（確認）</label>
      <input type="password" id="password_confirm" name="password_confirm" required>
    </div>

    <button type="submit"><i class="fa-solid fa-user-check"></i> 登録する</button>
  </form>

  <a href="login.php" class="login-link">すでにアカウントをお持ちの方はこちら ＞</a>
</div>

<footer>© 2025 愛媛大学 yuzurinプロジェクト</footer>

</body>
</html>
