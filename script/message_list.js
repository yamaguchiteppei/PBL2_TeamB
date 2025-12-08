// ==== 吹き出し追加 ====
function addMessage(text, sender) {
  const div = document.createElement("div");
  div.className = sender === "me" ? "message sent" : "message received";
  div.textContent = text;
  document.getElementById("chatMessages").appendChild(div);
}

// ==== メッセージ送信 ====
function sendMessage() {
  const input = document.getElementById("messageInput");
  const text = input.value.trim();
  if (!text) return;
  addMessage(text, "me");
  input.value = "";
  console.log("📨 送信デモ:", text);
}

// ==== クリック遷移 ====
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".chat-item").forEach((item) => {
    item.addEventListener("click", () => {
      const s = item.dataset.seller;
      const b = item.dataset.book;
      window.location.href = `message_list.php?seller=${encodeURIComponent(s)}&book=${encodeURIComponent(b)}`;
    });
  });
  const btn = document.getElementById("sendBtn");
  if (btn) btn.addEventListener("click", sendMessage);
  
  // 選択されたチャットアイテムを自動スクロール
  const activeItem = document.querySelector(".chat-item.active");
  if (activeItem) {
    activeItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
});
