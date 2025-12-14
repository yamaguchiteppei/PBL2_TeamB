// ==== 吹き出し追加 ====
function addMessage(text, sender, time) {
  const div = document.createElement("div");
  div.className = sender === "me" ? "message sent" : "message received";
  div.textContent = text;
  if (time) {
    const timeSpan = document.createElement("span");
    timeSpan.className = "message-time";
    timeSpan.textContent = time;
    timeSpan.style.fontSize = "0.75rem";
    timeSpan.style.color = "#999";
    timeSpan.style.marginLeft = "8px";
    div.appendChild(timeSpan);
  }
  const messagesDiv = document.getElementById("chatMessages");
  if (messagesDiv) {
    messagesDiv.appendChild(div);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  }
}

// ==== URLパラメータからsellerとbookを取得 ====
function getChatInfo() {
  // URLパラメータから取得を試みる
  const urlParams = new URLSearchParams(window.location.search);
  let seller = urlParams.get('seller');
  let book = urlParams.get('book');
  
  // URLパラメータがない場合、activeなchat-itemから取得
  if (!seller || !book) {
    const activeItem = document.querySelector(".chat-item.active");
    if (activeItem) {
      seller = activeItem.dataset.seller;
      book = activeItem.dataset.book;
    }
  }
  
  return { seller, book };
}

// ==== チャット履歴読み込み ====
function loadChatHistory() {
  const { seller, book } = getChatInfo();
  if (!seller || !book) return;
  
  const key = `${seller}_${book}`;
  fetch(`message_api.php?load_chat=${encodeURIComponent(key)}`)
    .then(res => res.json())
    .then(messages => {
      const messagesDiv = document.getElementById("chatMessages");
      if (messagesDiv) {
        messagesDiv.innerHTML = "";
        messages.forEach(msg => {
          addMessage(msg.text, msg.sender, msg.time);
        });
      }
    })
    .catch(err => {
      console.error("履歴読み込みエラー:", err);
    });
}

// ==== メッセージ送信 ====
function sendMessage() {
  const input = document.getElementById("messageInput");
  const text = input.value.trim();
  if (!text) return;
  
  const { seller, book } = getChatInfo();
  if (!seller || !book) {
    alert("エラー: 送信先情報が取得できません");
    return;
  }
  
  // フォームデータを作成
  const formData = new FormData();
  formData.append("seller", seller);
  formData.append("book", book);
  formData.append("message", text);
  
  // APIに送信
  fetch("message_api.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "ok") {
        input.value = "";
        // 履歴を再読み込み
        loadChatHistory();
      } else {
        alert("送信に失敗しました: " + (data.msg || "不明なエラー"));
      }
    })
    .catch(err => {
      console.error("送信エラー:", err);
      alert("送信に失敗しました");
    });
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
  if (btn) {
    btn.addEventListener("click", sendMessage);
  }
  
  const input = document.getElementById("messageInput");
  if (input) {
    input.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        sendMessage();
      }
    });
  }
  
  // チャット履歴を読み込む
  const { seller, book } = getChatInfo();
  if (seller && book) {
    loadChatHistory();
  }
  
  // 選択されたチャットアイテムを自動スクロール
  const activeItem = document.querySelector(".chat-item.active");
  if (activeItem) {
    activeItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
});
