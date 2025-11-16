<?php
session_start();

// 🔐 ログイン確認
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['user']['username'];
$dataDir  = __DIR__ . '/data/profiles';
if (!file_exists($dataDir)) mkdir($dataDir, 0777, true);
$dataFile = "{$dataDir}/{$username}.json";

// === 入力データを取得 ===
$display_name = trim($_POST['display_name'] ?? '');
$faculty      = trim($_POST['faculty'] ?? '');
$bio          = trim($_POST['bio'] ?? '');
$usernameForm = trim($_POST['username'] ?? $username);

// === 既存データを読み込み ===
$profile = file_exists($dataFile)
    ? json_decode(file_get_contents($dataFile), true)
    : [];

// === 画像アップロード ===
$avatarPath = $profile['avatar'] ?? 'images/sample_avatar.png';
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . "/uploads/avatars/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $tmpName = $_FILES['avatar']['tmp_name'];
    $imageInfo = getimagesize($tmpName);
    if ($imageInfo !== false) {
        // ファイル名はユーザー名に固定
        $targetFile = $uploadDir . "avatar_" . $username . ".png";
        $image = imagecreatefromstring(file_get_contents($tmpName));
        imagepng($image, $targetFile);
        imagedestroy($image);
        $avatarPath = "uploads/avatars/avatar_" . $username . ".png";
    }
}

// === JSONに保存 ===
$profile = [
    'display_name' => $display_name,
    'username'     => $usernameForm,
    'faculty'      => $faculty,
    'bio'          => $bio,
    'avatar'       => $avatarPath,
    'updated_at'   => date('Y-m-d H:i:s')
];

file_put_contents($dataFile, json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// ✅ 保存完了後リダイレクト
header("Location: profile.php?saved=1");
exit;
?>