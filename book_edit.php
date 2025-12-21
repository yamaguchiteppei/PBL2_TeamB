<?php
require __DIR__ . '/php/auth.php';
require_login();

$books_file = __DIR__ . '/books.json';
$books = file_exists($books_file) ? json_decode(file_get_contents($books_file), true) : [];

$index = filter_input(INPUT_GET, 'index', FILTER_VALIDATE_INT);
if ($index === false || !isset($books[$index])) {
  die('対象の教科書が見つかりません');
}

$book = $books[$index];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $books[$index]['title'] = trim($_POST['title']);
  $books[$index]['trade'] = $_POST['trade'];
  $books[$index]['price'] = ($_POST['trade'] === 'paid') ? (int)$_POST['price'] : 0;
  $books[$index]['faculty'] = $_POST['faculty'];
  $books[$index]['department'] = $_POST['department'];
  $books[$index]['course'] = $_POST['course'];
  $books[$index]['detail'] = trim($_POST['detail']);
    
  if (!empty($_FILES['book_image']['name'])) {
    $ext = pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('book_', true) . '.' . $ext;
    $path = 'uploads/' . $filename;

    move_uploaded_file($_FILES['book_image']['tmp_name'], $path);
    $books[$index]['image'] = $path;
  }

  file_put_contents(
    $books_file,
    json_encode($books, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
  );

  header('Location: book_list.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>教科書編集</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style/book_upload.css">
<script src="script/book_upload.js" defer></script>
</head>

<body>

<h2 class="page-title">📘 教科書情報の編集</h2>

<form method="post" enctype="multipart/form-data">

<div class="form-group">
    <label>・教科書名</label>
    <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
</div>
<div class="form-group">
    <label>・教科書画像（変更する場合のみ）</label><br>
    <img src="<?= htmlspecialchars($book['image']) ?>" width="120"><br><br>
    <input type="file" name="book_image" accept="image/*">
</div>

<div class="form-group">
    <label>・譲渡</label><br>
    <label>
        <input type="radio" name="trade" value="free"
        <?= $book['trade']==='free'?'checked':'' ?>> 無償提供（OK!）
    </label><br>
    <label>
        <input type="radio" name="trade" value="paid"
        <?= $book['trade']==='paid'?'checked':'' ?>> 有償取引を希望
    </label>

    <div id="priceField" style="<?= $book['trade']==='paid'?'':'display:none;' ?>">
      <span> 希望価格：</span>
      <input type="number" name="price" value="<?= htmlspecialchars($book['price']) ?>" min="0" step="100" placeholder="例：1000"> 円
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
    <textarea name="detail" rows="5" placeholder="例:表紙に書き込みがあります。" style="width: 100%; padding: 10px; box-sizing: border-box;"><?= htmlspecialchars($book['detail'] ?? '') ?></textarea>
</div>

<button type="submit" class="submit-btn">更新する</button>
</form>

</body>
</html>
