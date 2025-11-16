// ページ読み込み後に画像の存在確認・プレビュー制御などを行う例
document.addEventListener("DOMContentLoaded", () => {
  const img = document.getElementById("bookImage");
  img.onerror = () => {
    img.src = "images/sample_book.jpg";
  };
});
