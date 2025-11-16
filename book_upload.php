<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>教科書出品</title>
<link rel="stylesheet" href="style/book_upload.css">
<script src="script/book_upload.js" defer></script>
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

            <div id="priceField">
                <span> 希望価格：</span>
                <input type="number" name="price" min="0" step="100" placeholder="例：1000"> 円
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>・使用した学部・学科</label>
        <select name="faculty">
            <option value="">選択する</option>
            <option>共通教育</option>
            <option>工学部　応用情報コース</option>
            <option>工学部　コンピュータ科学コース</option>
            <option>教育学部</option>
            <option>法文学部</option>
        </select>
    </div>

    <button type="submit" class="submit-btn">確認</button>
</form>
</body>
</html>
