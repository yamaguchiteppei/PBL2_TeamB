// デモ用の簡易スクリプト（本番では不要）
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  if (form) {
    form.addEventListener("submit", (e) => {
      const username = form.querySelector("input[name='username']").value;
      if (username.trim() === "") {
        e.preventDefault();
        alert("学籍番号を入力してください。");
      }
    });
  }
});
