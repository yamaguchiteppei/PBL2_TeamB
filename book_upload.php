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
            <option>工学部　情報分野</option>
            <option>工学部　機械・システム分野</option>
            <option>工学部　電気分野</option>
            <option>工学部　材料・化学分野</option>
            <option>工学部　土木・環境分野</option>
            <option>理学部　数学・数理情報コース</option>
            <option>理学部　物理学コース</option>
            <option>理学部　化学コース</option>
            <option>理学部　生物学コース</option>
            <option>理学部　地学コース</option>
            <option>農学部　食糧生産学科</option>
            <option>農学部　生命機能学科</option>
            <option>農学部　生物環境学科</option>
            <option>医学部　医学科</option>
            <option>医学部　看護学科</option>
            <option>法文部　法学・政策学履修コース</option>
            <option>法文部　グローバル・スタディーズ履修コース</option>
            <option>法文部　人文学履修コース</option>
            <option>教育学部　教育発達実践コース</option>
            <option>教育学部　初等中等教育コース</option>
            <option>社会共創学部　産業マネジメント学科</option>
            <option>社会共創学部　産業イノベーション学科</option>
            <option>社会共創学部　環境デザイン学科</option>
            <option>社会共創学部　地域資源マネジメント学科</option>
        </select>
    </div>

    <button type="submit" class="submit-btn">確認</button>
</form>
</body>
</html>
