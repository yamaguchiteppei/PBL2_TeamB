(function(){
  const normalize = (s) => (s || '').replace(/\u3000/g, ' ').trim().toLowerCase();

  function setupSearch(inputId, clearId, groupName, nohitId) {
    const input = document.getElementById(inputId);
    const clearBtn = document.getElementById(clearId);
    const items = Array.from(document.querySelectorAll(`.book-item[data-group="${groupName}"]`));
    const nohit = document.getElementById(nohitId);

    const filter = () => {
      const q = normalize(input.value);
      let shown = 0;
      items.forEach(el => {
        const hay = el.getAttribute('data-search') || '';
        const hit = q === '' || hay.indexOf(q) !== -1;
        el.classList.toggle('hidden', !hit);
        if (hit) shown++;
      });
      nohit.classList.toggle('hidden', shown > 0 || q === '');
    };

    input.addEventListener('input', filter);
    clearBtn.addEventListener('click', e => { e.preventDefault(); input.value = ''; input.focus(); filter(); });
    filter();
  }

  setupSearch('searchOthers', 'clearOthers', 'others', 'nohitOthers');
  setupSearch('searchMine', 'clearMine', 'mine', 'nohitMine');

  // カード全体をクリックで詳細へ遷移（ボタン・リンクは除外）
  const cards = document.querySelectorAll('.book-item[data-detail-url]');
  cards.forEach(card => {
    card.style.cursor = 'pointer';
    card.addEventListener('click', e => {
      const interactive = e.target.closest('button, a, form, input, select, textarea, label');
      if (interactive) return; // ボタンやリンク操作を優先
      const url = card.getAttribute('data-detail-url');
      if (url) {
        window.location.href = url;
      }
    });
  });
})();
