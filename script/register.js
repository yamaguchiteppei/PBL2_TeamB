function checkPasswords() {
  const pw = document.getElementById("password").value;
  const confirm = document.getElementById("password_confirm").value;

  if (pw.length < 8) {
    alert("パスワードは8文字以上で入力してください。");
    return false;
  }
  if (pw !== confirm) {
    alert("パスワードが一致しません。");
    return false;
  }
  return true;
}

document.addEventListener("DOMContentLoaded", () => {
  console.log("register.js loaded ✅");
});
