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

function makeKey(seller, buyer, book) {
  const users = [seller, buyer].sort();
  return `${users[0]}_${users[1]}_${book}`;
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

  // --- 通報ボタン（自分のメッセージ以外） ---
  if (!isMe) {
    const reportBtn = document.createElement("button");
    reportBtn.className = "message-report-btn";
    reportBtn.innerHTML = "⚠️";
    reportBtn.title = "このメッセージを通報";
    reportBtn.setAttribute("data-seller", msg.sender || "");
    reportBtn.setAttribute("data-text", msg.text || "");
    reportBtn.setAttribute("data-time", msg.time || "");
    reportBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      handleMessageReport(reportBtn, msg);
    });
    row.appendChild(reportBtn);
  }

  div.appendChild(row);
  div.className = "message-wrapper";
  div.setAttribute("data-sender", msg.sender || "");
  div.setAttribute("data-time", msg.time || "");

  const container = document.getElementById("chatMessages");
  container.appendChild(div);
  container.scrollTop = container.scrollHeight;
}







// ==== チャット履歴読み込み ====
async function loadChat(seller,buyer,book) {
  const key = makeKey(seller, buyer, book);
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

  const btn = document.getElementById('sendBtn');
  if (btn) btn.disabled = true;

  const header = document.querySelector('.chat-header');
  const seller = header?.dataset.seller || '';
  const buyer  = header?.dataset.buyer  || '';
  const book   = header?.dataset.book   || '';

  if (!seller || !buyer || !book) {
    console.warn('送信先情報がありません', { seller, buyer, book });
    if (btn) btn.disabled = false;
    return;
  }

  // ★★★ ここが最重要 ★★★
  const chat_key = makeKey(seller, buyer, book);

  // 楽観的UI
  const tempTime = new Date().toISOString().slice(0, 19).replace('T', ' ');
  addMessage({
    text,
    time: tempTime,
    sender: CURRENT_USER,
    is_me: true
  });

  const container = document.getElementById("chatMessages");
  const addedEl = container.lastElementChild;

  try {
    const body =
      `chat_key=${encodeURIComponent(chat_key)}` +
      `&message=${encodeURIComponent(text)}`;

    const res = await fetch('send_message.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body
    });

    const data = await res.json();

    if (data.status === 'success') {
      input.value = '';
        loadChat(seller, buyer, book);
      return;
    }

    throw new Error('send failed');

  } catch (e) {
    if (addedEl) addedEl.remove();
    alert('送信に失敗しました');
    console.error(e);
  } finally {
    if (btn) btn.disabled = false;
  }
}



// ==== メッセージ通報処理（個別メッセージ用） ====
function handleMessageReport(buttonEl, msg) {
  // sellerとbookを取得
  const header = document.querySelector('.chat-header');
  if (!header) {
    alert('チャット情報が取得できません');
    return;
  }
  
  // data属性から取得（優先）
  const book = header.dataset.book || (header.querySelector('.chat-book-title')?.textContent.trim() || '');
  const seller = header.dataset.seller || (header.querySelector('.seller-account')?.textContent.match(/（(.+)）/)?.[1] || '');
  
  if (!seller || !book) {
    alert('チャット情報が取得できません');
    return;
  }

  // 確認ダイアログ
  const confirmMsg = `このメッセージを通報しますか？\n\n送信者: ${msg.sender || '不明'}\n内容: ${(msg.text || '').substring(0, 50)}${(msg.text || '').length > 50 ? '...' : ''}`;
  if (!confirm(confirmMsg)) {
    return;
  }

  // 通報理由を入力（オプション）
  const reason = prompt('通報理由を入力してください（任意）:');
  if (reason === null) return; // キャンセル

  // UI更新
  buttonEl.disabled = true;
  buttonEl.textContent = '送信中...';
  buttonEl.style.opacity = '0.6';

  // 通報実行
  reportMessage(seller, book, msg.text || '', msg.time || '', msg.sender || '', buttonEl, reason || '')
    .then(() => {
      buttonEl.textContent = '通報済み';
      buttonEl.style.opacity = '0.5';
    })
    .catch((e) => {
      console.error('通報エラー', e);
      buttonEl.disabled = false;
      buttonEl.innerHTML = '⚠️';
      buttonEl.style.opacity = '1';
    });
}

// ==== 通報処理 ====
async function reportMessage(seller, book, text, time, original_sender, buttonEl, reason = '') {
  try {
    const body = `action=report&seller=${encodeURIComponent(seller)}&book=${encodeURIComponent(book)}&text=${encodeURIComponent(text)}&time=${encodeURIComponent(time)}&original_sender=${encodeURIComponent(original_sender)}&reason=${encodeURIComponent(reason)}`;
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
      const errorMsg = data.msg || '不明なエラー';
      console.error('通報エラー:', errorMsg, data);
      alert('通報に失敗しました: ' + errorMsg);
      if (buttonEl) {
        buttonEl.disabled = false;
        buttonEl.textContent = '⚠️';
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
  document.querySelectorAll('.chat-search').forEach(input => {
  input.addEventListener('input', () => {
    const keyword = input.value.trim().toLowerCase();
    const target = input.dataset.target; // seller / buyer

    const group = document.querySelector(`.${target}-group`);
    if (!group) return;

    group.querySelectorAll('.chat-item').forEach(item => {
      const book = (item.dataset.book || '').toLowerCase();
      const seller = (item.dataset.seller || '').toLowerCase();
      const buyer  = (item.dataset.buyer  || '').toLowerCase();

      const hit =
        book.includes(keyword) ||
        seller.includes(keyword) ||
        buyer.includes(keyword);
      item.style.display = hit ? 'flex' : 'none';
    });
  });
});
document.querySelectorAll(".chat-item").forEach((item) => {
  item.addEventListener("click", () => {
    const seller = item.dataset.seller;
    const buyer  = item.dataset.buyer;
    const book   = item.dataset.book;

    if (!seller || !buyer || !book) {
      console.warn('chat-item dataset 不足', { seller, buyer, book });
      return;
    }

    // chat-header を更新
    const header = document.querySelector('.chat-header');
    header.dataset.seller = seller;
    header.dataset.buyer  = buyer;
    header.dataset.book   = book;

    // active 切り替え
    document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('active'));
    item.classList.add('active');

    loadChat(seller, buyer, book);
    startPolling();
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

      document.querySelectorAll('.chat-group-title.toggle').forEach(title => {
  title.addEventListener('click', () => {
    const targetClass = title.dataset.target;
    const group = document.querySelector(`.${targetClass}`);
    const search = title.nextElementSibling; // 検索バー

    if (!group || !search) return;

    const isClosed = group.classList.toggle('collapsed');
    search.classList.toggle('collapsed', isClosed);

    title.classList.toggle('closed', isClosed);
  });
});
    });
    document.querySelectorAll('.chat-group-title.toggle').forEach(title => {
  title.addEventListener('click', () => {
    const targetClass = title.dataset.target;
    const group = document.querySelector(`.${targetClass}`);
    const search = title.nextElementSibling; // 検索バー

    if (!group || !search) return;

    const isClosed = group.classList.toggle('collapsed');
    search.classList.toggle('collapsed', isClosed);

    title.classList.toggle('closed', isClosed);
  });
});
    
  }

  // ヘッダーの通報ボタン（上の出品者名の横）
  const headerReport = document.getElementById('reportChatBtn');
  if (headerReport) {
    headerReport.addEventListener('click', () => {
      const header = document.querySelector('.chat-header');
      if (!header) {
        alert('チャット情報が取得できません');
        return;
      }
      // data属性から取得（優先）
      const book = header.dataset.book || (header.querySelector('.chat-book-title')?.textContent.trim() || '');
      const seller = header.dataset.seller || (header.querySelector('.seller-account')?.textContent.match(/（(.+)）/)?.[1] || '');
      if (!seller || !book) {
        alert('対象チャットが特定できません');
        return;
      }
      // 通報するメッセージを入力させる（省略可）
      const text = prompt('通報するメッセージの本文を入力してください（空欄ならアカウント通報）');
      if (text === null) return; // キャンセル
      const original_sender = prompt('通報対象の発言者のユーザー名を入力してください（不明なら空欄）');
      const reason = prompt('通報理由を入力してください（任意）:');
      if (reason === null) return; // キャンセル
      // UI フィードバック
      headerReport.disabled = true;
      headerReport.textContent = '送信中...';
      console.log('通報送信開始', {seller, book, text, original_sender, reason});
      reportMessage(seller, book, text || '', '', original_sender || '', headerReport, reason || '').then(() => {
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
    // data属性から取得（優先）
    const book = header.dataset.book || (header.querySelector('.chat-book-title')?.textContent.trim() || '');
    const seller = header.dataset.seller || (header.querySelector('.seller-account')?.textContent.match(/（(.+)）/)?.[1] || '');
    const buyer = header.dataset.buyer;
    if (book && seller && buyer) {
      loadChat(seller,buyer,book);
      startPolling();
    }
  
  // 選択されたチャットアイテムを自動スクロール
  const activeItem = document.querySelector(".chat-item.active");
  if (activeItem) {
    activeItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
  }
});


document.addEventListener('click', (e) => {
  const title = e.target.closest('.chat-group-title.toggle');
  if (!title) return;

  const target = title.dataset.target;
  const group = document.querySelector(`.${target}-group`);
  const search = document.querySelector(`.chat-search[data-target="${target}"]`);

  // group がなくても落ちない
  if (group) group.classList.toggle('collapsed');
  if (search) search.classList.toggle('collapsed');

  title.classList.toggle('closed');
});

let pollingTimer = null;

function startPolling() {
  if (pollingTimer) clearInterval(pollingTimer);

  pollingTimer = setInterval(() => {
    const header = document.querySelector('.chat-header');
    if (!header) return;

    const seller = header.dataset.seller;
    const buyer  = header.dataset.buyer;
    const book   = header.dataset.book;

    if (seller && buyer && book) {
      loadChat(seller, buyer, book);
    }
  }, 3000); // 3秒
}
