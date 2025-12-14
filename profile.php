<?php
require __DIR__ . '/php/auth.php';
require_login();

$sessionUser  = $_SESSION['user']['username'];
$sessionEmail = $_SESSION['user']['email'];

// ===== 各ユーザー専用プロフィールファイル =====
$dataDir  = __DIR__ . '/data/profiles';
if (!file_exists($dataDir)) mkdir($dataDir, 0777, true);
$dataFile = "{$dataDir}/{$sessionUser}.json";

// ===== JSON読み込み =====
$profile = file_exists($dataFile)
    ? json_decode(file_get_contents($dataFile), true)
    : [];

// ===== 保存処理（POST送信時） =====
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $display_name = trim($_POST['display_name'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $faculty      = trim($_POST['faculty'] ?? '');
    $bio          = trim($_POST['bio'] ?? '');
    $avatar_path  = $profile['avatar'] ?? 'images/default.jpg';

    // ===== アバター画像の保存処理 =====
    if (!empty($_FILES['avatar']['name'])) {
      // 他の場所と揃えて uploads/avatars に保存する
      $upload_dir = __DIR__ . '/uploads/avatars';
      if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

      $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
      // ファイル名は他と同様に avatar_{username}.png 形式に統一
      $new_name = 'avatar_' . $sessionUser . '.' . $ext;
      $target = $upload_dir . '/' . $new_name;

      if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
        $avatar_path = 'uploads/avatars/' . $new_name;
      }
    }

    // ===== JSONに保存 =====
    $new_profile = [
        'display_name' => $display_name ?: '名無し',
        'username'     => $username ?: $sessionUser,
        'faculty'      => $faculty,
        'bio'          => $bio,
        'avatar'       => $avatar_path
    ];
    file_put_contents($dataFile, json_encode($new_profile, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    $profile = $new_profile; // 即反映
    $saved = true;           // 保存メッセージ表示用
}

// ===== 表示用変数 =====
$display_name = htmlspecialchars($profile['display_name'] ?? '名無し', ENT_QUOTES, 'UTF-8');
$username     = htmlspecialchars($profile['username'] ?? $sessionUser, ENT_QUOTES, 'UTF-8');
$faculty      = htmlspecialchars($profile['faculty'] ?? '', ENT_QUOTES, 'UTF-8');
$bio          = htmlspecialchars($profile['bio'] ?? '', ENT_QUOTES, 'UTF-8');
$avatar       = htmlspecialchars($profile['avatar'] ?? 'images/default.jpg', ENT_QUOTES, 'UTF-8');
$emailFull    = $username ? ($username . '@mails.cc.ehime-u.ac.jp') : '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>プロフィール編集 | yuzurin</title>
<link rel="stylesheet" href="style/profile.css">
<script src="https://kit.fontawesome.com/a4f2e2c2ef.js" crossorigin="anonymous"></script>
<style>
.notice-box {
  background-color: #e7f5e9;
  color: #137333;
  font-weight: bold;
  text-align: center;
  padding: 12px;
  border-radius: 8px;
  margin: 20px auto;
  max-width: 600px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
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
    <button class="active">プロフィール</button>
  </nav>
</header>

<div class="container">
  <div class="header-box">
    <h2><i class="fa-solid fa-user-pen"></i> プロフィール</h2>
  </div>

  <?php if (!empty($saved)): ?>
    <div class="notice-box">✅ プロフィールを更新しました！</div>
  <?php endif; ?>

  <div class="content">
    <!-- 左：現在のプロフィール表示 -->
    <div class="left">
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
        <p class="hint">※ 保存済み情報を表示しています。</p>
      </div>
    </div>

    <!-- 右：編集フォーム -->
    <div class="right">
      <form class="card" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label><i class="fa-solid fa-signature"></i> 表示名</label>
          <input type="text" name="display_name" value="<?= $display_name ?>" placeholder="愛大 太郎">
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-envelope"></i> 愛媛大学メールアドレス</label>
          <div class="input-inline">
            <input type="text" name="username" value="<?= $username ?>" placeholder="k000000x" readonly>
            <span>@mails.cc.ehime-u.ac.jp</span>
          </div>
          <div class="hint">ユーザー名のみを入力してください。</div>
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-graduation-cap"></i> 学部</label>
          <select name="faculty">
            <?php
            $opts = ['','法文学部','教育学部','社会共創学部','理学部','医学部','工学部','農学部','表示しない'];
            foreach ($opts as $opt) {
              $sel = ($faculty === $opt) ? 'selected' : '';
              $label = $opt ?: '選択してください';
              echo "<option value=\"".htmlspecialchars($opt,ENT_QUOTES,'UTF-8')."\" $sel>$label</option>";
            }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-comment-dots"></i> 自己紹介</label>
          <textarea name="bio" placeholder="研究分野・得意科目・一言など"><?= $bio ?></textarea>
        </div>

        <div class="form-group">
          <label><i class="fa-solid fa-image"></i> アバター画像（任意）</label>
          <input type="file" name="avatar" accept="image/*">
          <div class="hint">正方形推奨 / 2MB以下</div>
        </div>

        <div class="actions">
          <button type="reset" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> 破棄</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> 保存する</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>