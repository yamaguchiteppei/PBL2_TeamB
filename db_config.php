<?php
$dsn = 'mysql:host=127.0.0.1;dbname=login_system;charset=utf8mb4';
$user = 'root';
$pass = ''; // XAMPPなら空欄、MAMPなら 'root' の場合も

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('DB接続エラー：' . $e->getMessage());
}
?>