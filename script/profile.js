// ✅ 保存メッセージ表示
function showSaveNotice() {
  const notice = document.getElementById("saveNotice");
  notice.style.display = "block";
  setTimeout(() => {
    notice.style.display = "none";
  }, 2500);
}

// ✅ デモ時のログ
document.addEventListener("DOMContentLoaded", () => {
  console.log("profile.js loaded ✅");
});
