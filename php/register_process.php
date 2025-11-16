// ユーザー名を取得
$username = trim($_POST['username']);

// 入力チェック
if (empty($username)) {
    die("ユーザー名が未入力です。");
}

// 実際の宛先メールアドレスを生成
$email = $username . "@mails.cc.ehime-u.ac.jp";

// 認証用トークンを生成（セキュアに）
$token = bin2hex(random_bytes(16));

// トークンを一時的に保存（簡易例：ファイル保存／実際はDB推奨）
file_put_contents("tokens/$token.txt", $email);

// 認証URLを作成
$verifyUrl = "https://example.com/verify.php?token=" . urlencode($token);

// メール内容
$subject = "【愛媛大学】アカウント登録確認";
$message = <<<EOT
{$username} さん

以下のリンクをクリックして登録を完了してください。

▼ 登録確認リンク
{$verifyUrl}

このメールに心当たりがない場合は破棄してください。
EOT;

// 送信ヘッダー
$headers = "From: no-reply@ehime-u.ac.jp";

// メール送信
if (mail($email, $subject, $message, $headers)) {
    echo "確認メールを {$email} に送信しました。メールを確認してください。";
} else {
    echo "メール送信に失敗しました。";
}
?>