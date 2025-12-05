<?php
// 共通認証ユーティリティ
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * ログイン必須ページで呼ぶ。未ログインなら login.php にリダイレクトする。
 */
function require_login() {
    if (!isset($_SESSION['user'])) {
        // 元のアクセス先を保存しておく（ログイン後に戻す場合に使用）
        $_SESSION['after_login_redirect'] = $_SERVER['REQUEST_URI'] ?? null;
        // フラッシュメッセージ
        $_SESSION['flash'] = 'このページはログインが必要です。ログインしてください。';
        header('Location: /yuzurin2/login.php');
        exit;
    }
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}
