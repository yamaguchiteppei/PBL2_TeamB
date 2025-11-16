document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  if (form) {
    form.addEventListener("submit", (e) => {
      const pw = form.querySelector("input[name='new_password']").value;
      if (pw.length < 6) {
        e.preventDefault();
        alert("パスワードは6文字以上で設定してください。");
      }
    });
  }
});
