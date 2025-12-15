// ==== 時刻パース関数 ====
// 例: "2025-12-14 15:22:30" → Date オブジェクトに変換
function parseTime(t) {
  if (!t) return new Date();
  // "YYYY-MM-DD HH:MM:SS" を Date に変換
  const parts = t.split(' ');
  if (parts.length === 2) {
    return new Date(parts[0] + "T" + parts[1]);
  }
  return new Date(t);
}


// ==== 日付の区切りを追加 ====
function addDateSeparator(dateText) {
  const sep = document.createElement("div");
  sep.className = "date-separator";
  sep.textContent = dateText;

  const container = document.getElementById("chatMessages");
  container.appendChild(sep);
}

// ==== 吹き出し追加 ====
function addMessage(msg) {
  const div = document.createElement("div");

  const isMe = msg.is_me === true ||
    (msg.sender && CURRENT_USER && msg.sender === CURRENT_USER);

  const row = document.createElement("div");
  row.className = isMe ? "row me" : "row other";

  // --- 相手アバター ---
  if (!isMe) {
    const avatar = document.createElement("img");

    // 相手のアバター推測
    // --- PHP と同じ：英数字以外を除去したアカウント名を使う ---
    const rawName = msg.sender || "";
    const safeName = rawName.replace(/[^a-zA-Z0-9]/g, "");
const base = `uploads/avatars/avatar_${safeName}`;
const tryExt = ["jpg", "jpeg", "png"];

function tryLoadAvatar(index = 0) {
  if (index >= tryExt.length) {
    avatar.src = "images/default.jpg";
    return;
  }
  const tryPath = `${base}.${tryExt[index]}`;
  avatar.src = tryPath;

  avatar.onerror = () => tryLoadAvatar(index + 1);
}

// 画像読み込み開始
tryLoadAvatar();

    avatar.onerror = () => { avatar.src = "images/default.jpg"; };  
    avatar.className = "chat-avatar-small";

    row.appendChild(avatar);
  }

  // --- バブル ---
  const bubble = document.createElement("div");
  bubble.className = isMe ? "message-bubble me" : "message-bubble other";

  const name = document.createElement("div");
  name.className = "sender-name";
  name.textContent = msg.sender || "???";
  bubble.appendChild(name);

  const text = document.createElement("div");
  text.className = "message-text";
  text.textContent = msg.text;
  bubble.appendChild(text);

  row.appendChild(bubble);

  // --- 時刻 ---
  const dateObj = parseTime(msg.time);
  const time = document.createElement("div");
  time.className = isMe ? "msg-time me" : "msg-time other";
  time.textContent = dateObj instanceof Date && !isNaN(dateObj)
    ? dateObj.toTimeString().slice(0,5)
    : "";
  row.appendChild(time);

  div.appendChild(row);

  const container = document.getElementById("chatMessages");
  container.appendChild(div);
  container.scrollTop = container.scrollHeight;
}







// ==== チャット履歴読み込み ====
async function loadChat(seller, book) {
  const key = `${seller}_${book}`;
  try {
    const res = await fetch(`message_api.php?load_chat=${encodeURIComponent(key)}`, { credentials: 'same-origin' });
    if (!res.ok) return;

    const messages = await res.json();
    const container = document.getElementById("chatMessages");
    container.innerHTML = '';

    let lastDate = null;


messages.forEach(m => {
      const msgDate = new Date(m.time);
      const yyyyMMdd = msgDate.toISOString().slice(0, 10);
      const today = new Date();
      const todayStr = today.toISOString().slice(0, 10);

      const yesterday = new Date();
      yesterday.setDate(today.getDate() - 1);
      const yesterdayStr = yesterday.toISOString().slice(0, 10);

      // --- 日付が変わったら区切りを追加 ---
      if (yyyyMMdd !== lastDate) {
        let label = yyyyMMdd;

        if (yyyyMMdd === todayStr) label = "今日";
        else if (yyyyMMdd === yesterdayStr) label = "昨日";

        addDateSeparator(label);
        lastDate = yyyyMMdd;
      }

      addMessage(m);
    });

    // 既読化
    await fetch(`message_api.php?mark_read=${encodeURIComponent(key)}`, {
      credentials: 'same-origin'
    });

  } catch (e) {
    console.error("チャット読み込み失敗", e);
  }
}



// ==== メッセージ送信 ====
async function sendMessage() {
  const input = document.getElementById("messageInput");
  const text = input.value.trim();
  if (!text) return;
  // 送信中はボタンを無効化
  const btn = document.getElementById('sendBtn');
  if (btn) btn.disabled = true;

  // seller / book 情報を DOM から取得
  const header = document.querySelector('.chat-header');
  // 優先: data-* 属性（売却バッジなどで textContent が変わるのを避ける）
  const book = header ? (header.dataset.book || (header.querySelector('h2')?.textContent.trim() || '')) : '';
  const seller = header ? (header.dataset.seller || (header.querySelector('p')?.textContent.trim() || '')) : '';
  if (!seller || !book) {
    console.warn('送信先情報がありません');
    if (btn) btn.disabled = false;
    return;
  }

  // 楽観的に表示：先にチャット欄に追加してからサーバへ送信
const tempTime = new Date().toISOString().slice(0,19).replace('T',' ');
addMessage({ text, time: tempTime, sender: CURRENT_USER, is_me: true });
  // 直前に追加した要素を保持しておく（失敗時に削除するため）
  const container = document.getElementById("chatMessages");
  const addedEl = container.lastElementChild;

  try {
    const body = `seller=${encodeURIComponent(seller)}&book=${encodeURIComponent(book)}&message=${encodeURIComponent(text)}`;
    const res = await fetch('message_api.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body
    });
    const data = await res.json();
    if (data && data.status === 'error' && data.msg === 'not_logged_in') {
      // 未ログイン：追加したメッセージを削除してログインへ
      if (addedEl && addedEl.parentNode) addedEl.parentNode.removeChild(addedEl);
      alert('ログインが必要です。ログインページに移動します。');
      location.href = 'login.php';
      return;
    }
    if (data.status === 'ok') {
      // 成功：入力欄をクリア
      input.value = '';
    } else {
      // 失敗：追加した要素を削除して通知
      if (addedEl && addedEl.parentNode) addedEl.parentNode.removeChild(addedEl);
      alert('送信に失敗しました');
    }
  } catch (e) {
    // ネットワークエラーなど
    if (addedEl && addedEl.parentNode) addedEl.parentNode.removeChild(addedEl);
    console.error('送信エラー', e);
    alert('送信エラーが発生しました');
  } finally {
    if (btn) btn.disabled = false;
  }
}

// ==== 通報処理 ====
async function reportMessage(seller, book, text, time, original_sender, buttonEl) {
  try {
    const body = `action=report&seller=${encodeURIComponent(seller)}&book=${encodeURIComponent(book)}&text=${encodeURIComponent(text)}&time=${encodeURIComponent(time)}&original_sender=${encodeURIComponent(original_sender)}`;
    const res = await fetch('message_api.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body
    });
    const data = await res.json();
    if (data && data.status === 'error' && data.msg === 'not_logged_in') {
      alert('ログインが必要です。ログインページに移動します。');
      location.href = 'login.php';
      return;
    }
    if (data.status === 'ok') {
      alert('通報しました。運営が確認します。');
      if (buttonEl) {
        buttonEl.textContent = '通報済み';
        buttonEl.disabled = true;
      }
    } else {
      alert('通報に失敗しました');
      if (buttonEl) {
        buttonEl.disabled = false;
        buttonEl.textContent = '通報';
      }
    }
  } catch (e) {
    console.error('通報エラー', e);
    console.error(e);
    alert('通報中にエラーが発生しました');
    if (buttonEl) {
      buttonEl.disabled = false;
      buttonEl.textContent = '通報';
    }
  }
}

// ==== クリック遷移と初期読み込み ====
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".chat-item").forEach((item) => {
    item.addEventListener("click", () => {
      const s = item.dataset.seller;
      const b = item.dataset.book;
      const url = `message_list.php?seller=${encodeURIComponent(s)}&book=${encodeURIComponent(b)}`;
      location.href = url;
      window.location.href = `message_list.php?seller=${encodeURIComponent(s)}&book=${encodeURIComponent(b)}`;
    });
  });

  const btn = document.getElementById("sendBtn");
  if (btn) btn.addEventListener("click", sendMessage);

  // Enter キーで送信（Shift+Enter で改行）
  const inputEl = document.getElementById('messageInput');
  if (inputEl) {
    inputEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        // 送信ボタンが無効化されている場合は何もしない
        const btnEl = document.getElementById('sendBtn');
        if (btnEl && btnEl.disabled) return;
        sendMessage();
      }
    });
  }

  // ヘッダーの通報ボタン（上の出品者名の横）
  const headerReport = document.getElementById('reportChatBtn');
  if (headerReport) {
    headerReport.addEventListener('click', () => {
      const headerBook = document.querySelector('.chat-header h2');
      const headerSeller = document.querySelector('.chat-header p');
      const book = headerBook ? headerBook.textContent.trim() : '';
      const seller = headerSeller ? headerSeller.textContent.trim() : '';
      if (!seller || !book) {
        alert('対象チャットが特定できません');
        return;
      }
      // 通報するメッセージを入力させる（省略可）
      const text = prompt('通報するメッセージの本文を入力してください（空欄ならアカウント通報）');
      if (text === null) return; // キャンセル
      const original_sender = prompt('通報対象の発言者のユーザー名を入力してください（不明なら空欄）');
      // UI フィードバック
      headerReport.disabled = true;
      headerReport.textContent = '送信中...';
      console.log('通報送信開始', {seller, book, text, original_sender});
      reportMessage(seller, book, text || '', '', original_sender || '', headerReport).then(() => {
        console.log('通報送信完了');
      }).catch((e) => {
        console.error('reportMessage で例外', e);
        // 何もしない。reportMessage 内で UI を復旧します。
      });
    });
  }

  // ページに seller/book が存在する場合は履歴を読み込む
  const header = document.querySelector('.chat-header');
  if (header) {
    const book = header.dataset.book || (header.querySelector('h2')?.textContent.trim() || '');
    const seller = header.dataset.seller || (header.querySelector('p')?.textContent.trim() || '');
    if (book && seller) loadChat(seller, book);
  
  // 選択されたチャットアイテムを自動スクロール
  const activeItem = document.querySelector(".chat-item.active");
  if (activeItem) {
    activeItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
  }
});



