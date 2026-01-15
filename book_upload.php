<?php
require __DIR__ . '/php/auth.php';
require_login();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>教科書出品</title>
<link rel="stylesheet" href="style/book_upload.css">
<script src="script/book_upload.js" defer></script>
<script src="https://kit.fontawesome.com/a4f2e2c2ef.js" crossorigin="anonymous"></script> 
</head>

<body>
<header>
    <nav class="menu">
        <button onclick="location.href='book_list.php'">購入画面</button>
        <button class="active">出品</button>
        <button onclick="location.href='message_list.php'">メッセージ</button>
        <button onclick="location.href='login.php'">ログイン</button>
        <button onclick="location.href='profile.php'">プロフィール</button>
    </nav>
</header>

<h2 class="page-title">📚 教科書出品フォーム</h2>

<form action="book_confirm.php" method="post" enctype="multipart/form-data">
    
    <div class="form-group">
        <label>・教科書名 <span class="required">*必須</span></label>
        <input type="text" name="book_name" placeholder="ここに入力" required>
    </div>

    <div class="form-group">
        <label>・教科書画像 <span class="required">*必須</span></label>
        <input type="file" name="book_image" accept="image/*" required>
    </div>

    <div class="form-group">
        <label>・譲渡</label>
        <div class="options">
            <label><input type="radio" name="trade" value="free" checked> 無償提供（OK!）</label><br>
            <label><input type="radio" name="trade" value="paid"> 有償取引を希望</label>

            <div id="priceField" style="display: none; margin-top: 10px;">
                <span> 希望価格：</span>
                <input type="number" name="price" min="0" step="100" placeholder="例：1000"> 円
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>・使用した学部</label>
        <select name="faculty" id="faculty_select">
            <option value="">選択してください</option>
            <option value="共通教育">共通教育</option>
            <option value="法文学部">法文学部</option>
            <option value="教育学部">教育学部</option>
            <option value="社会共創学部">社会共創学部</option>
            <option value="理学部">理学部</option>
            <option value="工学部">工学部</option>
            <option value="医学部">医学部</option>
            <option value="農学部">農学部</option>
        </select>
    </div>

    <div class="form-group">
        <label>・学科</label>
        <select name="department" id="department_select" disabled>
            <option value="">学部を選択してください</option>
        </select>
    </div>

    <div class="form-group">
        <label>・コース</label>
        <select name="course" id="course_select" disabled>
            <option value="">学科を選択してください</option>
        </select>
    </div>

    <div class="form-group">
        <label>・詳細情報（任意）</label>
        <textarea name="book_detail" rows="5" placeholder="例:表紙に書き込みがあります。" style="width: 100%; padding: 10px; box-sizing: border-box;"></textarea>
    </div>

    <button type="submit" class="submit-btn">確認</button>
</form>
</body>
</html>
