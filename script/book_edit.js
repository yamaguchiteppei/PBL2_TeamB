// ページ読み込み後の小さな動作（UI補助など）
document.addEventListener("DOMContentLoaded", () => {
  console.log("📘 book_edit.js loaded");
  
  // 例：変更検知
  const form = document.querySelector("form");
  form.addEventListener("input", () => {
    form.style.boxShadow = "0 0 10px rgba(0,75,151,0.2)";
  });
});
