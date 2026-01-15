<?php
session_start();

// ===== パラメータから閲覧対象ユーザー名を取得 =====
$view_user = isset($_GET['user']) ? trim($_GET['user']) : null;

if (!$view_user) {
    header("Location: book_list.php");
    exit;
}

// ===== 各ユーザー専用プロフィールファイル =====
$dataDir  = __DIR__ . '/data/profiles';
if (!file_exists($dataDir)) mkdir($dataDir, 0777, true);
$dataFile = "{$dataDir}/{$view_user}.json";

// ===== JSON読み込み =====
$profile = file_exists($dataFile)
    ? json_decode(file_get_contents($dataFile), true)
    : [];

// ===== 表示用変数 =====
$display_name = htmlspecialchars($profile['display_name'] ?? '名無し', ENT_QUOTES, 'UTF-8');
$username     = htmlspecialchars($profile['username'] ?? $view_user, ENT_QUOTES, 'UTF-8');
$faculty      = htmlspecialchars($profile['faculty'] ?? '', ENT_QUOTES, 'UTF-8');
$bio          = htmlspecialchars($profile['bio'] ?? '', ENT_QUOTES, 'UTF-8');
$avatar       = htmlspecialchars($profile['avatar'] ?? 'images/sample_avatar.png', ENT_QUOTES, 'UTF-8');
$emailFull    = $username ? ($username . '@mails.cc.ehime-u.ac.jp') : '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $display_name ?>のプロフィール | yuzurin</title>
<link rel="stylesheet" href="style/profile.css">
<script src="https://kit.fontawesome.com/a4f2e2c2ef.js" crossorigin="anonymous"></script>
<style>
.back-link {
  display: inline-block;
  margin-bottom: 20px;
  color: #0066cc;
  text-decoration: none;
  font-size: 14px;
}
.back-link:hover {
  text-decoration: underline;
}
</style>
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
  <a href="javascript:history.back()" class="back-link">← 戻る</a>

  <div class="header-box">
    <h2><i class="fa-solid fa-user"></i> プロフィール</h2>
  </div>

  <div class="content">
    <!-- プロフィール情報表示（編集機能なし） -->
    <div class="left" style="max-width: 100%; margin: 0 auto;">
      <div class="card">
        <div class="row center">
          <img src="<?= $avatar ?>" alt="アバター画像" class="avatar">
        </div>
        <div class="row"><div class="label">表示名</div><div class="value"><?= $display_name ?></div></div>
        <div class="row"><div class="label">ユーザー名</div><div class="value"><?= $username ?: '（未設定）' ?></div></div>
        <div class="row"><div class="label">大学メール</div>
          <div class="value">
            <?php if ($emailFull): ?>
              <span class="email-badge"><?= htmlspecialchars($emailFull, ENT_QUOTES, 'UTF-8') ?></span>
            <?php else: ?>(未設定)<?php endif; ?>
          </div>
        </div>
        <div class="row"><div class="label">学部</div><div class="value"><?= $faculty ?: '（未選択）' ?></div></div>
        <div class="row top">
          <div class="label">自己紹介</div>
          <div class="value"><?= nl2br($bio) ?: '（未入力）' ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
