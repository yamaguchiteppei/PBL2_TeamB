// 実際の宛先メールアドレスを生成
$email = $username . "@mails.cc.ehime-u.ac.jp";

// 認証用トークンを生成（セキュアに）
$token = bin2hex(random_bytes(16));

// トークンを一時的に保存（簡易例：ファイル保存／実際はDB推奨）
file_put_contents("tokens/$token.txt", $email);

// ===◆ サーバーの実際のURLを自動で取得する（推奨）◆===

// 1. http or https 判定
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

// 2. ホスト名を取得（例：localhost, 153.231.xxx.xxx, yoursite.com）
$host = $_SERVER['HTTP_HOST'];  

// 3. ベースURLを作成（例： http://localhost）
$baseUrl = $scheme . "://" . $host;

// 4. プロジェクトがサブフォルダにある場合、自動的にそのフォルダ名を付加
// （例： http://localhost/yuzurin → /yuzurin を追加）
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($scriptDir !== '') {
    $baseUrl .= $scriptDir;
}

// 5. 検証URLを最終生成
$verifyUrl = $baseUrl . "/verify.php?token=" . urlencode($token);

// =======================================================

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
