(() => {
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const $ = (sel, root = document) => root.querySelector(sel);
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const autoResize = (textarea) => {
    if (!textarea) return;
    textarea.style.height = 'auto';
    textarea.style.height = `${Math.min(textarea.scrollHeight, 160)}px`;
  };

  $$('textarea').forEach((ta) => {
    autoResize(ta);
    ta.addEventListener('input', () => autoResize(ta));
  });

  // Reveal on scroll
  const revealItems = $$('[data-reveal], .reveal-item');
  if (revealItems.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) entry.target.classList.add('is-visible');
      });
    }, { threshold: 0.12 });
    revealItems.forEach((el) => io.observe(el));
  }

  // Typing effects
  const typingTimers = new WeakMap();
  const typeInto = (el, text, speed = 32) => {
    if (!el) return;
    if (typingTimers.has(el)) clearTimeout(typingTimers.get(el));
    const original = String(text ?? '');
    if (reduceMotion) {
      el.textContent = original;
      el.classList.remove('typing-active');
      return;
    }
    el.classList.add('typing-active');
    el.textContent = '';
    let i = 0;
    const tick = () => {
      el.textContent = original.slice(0, i);
      i += 1;
      if (i <= original.length) {
        typingTimers.set(el, setTimeout(tick, speed));
      } else {
        el.classList.remove('typing-active');
      }
    };
    tick();
  };

  $$('[data-typing]').forEach((el) => {
    const text = el.textContent.trim();
    if (!text) return;
    typeInto(el, text, Number(el.dataset.typingSpeed || 34));
  });

  // Toasts + upgrade alerts
  const toastStack = $('#toastStack');
  const createToast = (message, type = 'success') => {
    if (!toastStack || !message) return;
    const toast = document.createElement('div');
    toast.className = `ui-toast ${type}`;
    const mark = document.createElement('span');
    mark.textContent = type === 'error' ? '!' : '✓';
    const body = document.createElement('div');
    body.textContent = message;
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.setAttribute('aria-label', 'Fechar');
    closeButton.textContent = '×';
    toast.append(mark, body, closeButton);
    const remove = () => {
      toast.classList.add('is-leaving');
      setTimeout(() => toast.remove(), 230);
    };
    closeButton.addEventListener('click', remove);
    toastStack.appendChild(toast);
    setTimeout(remove, 4600);
  };

  $$('.server-flashes [data-flash-type]').forEach((flash) => createToast(flash.textContent.trim(), flash.dataset.flashType || 'success'));
  $$('.alert').forEach((alert) => {
    if (alert.classList.contains('alert-enhanced')) return;
    const type = alert.classList.contains('alert-error') ? 'error' : 'success';
    createToast(alert.textContent.trim(), type);
    alert.classList.add('alert-enhanced');
    alert.style.display = 'none';
  });

  // Coming soon modal
  const comingModal = $('#comingModal');
  const comingTitle = $('#comingTitle');
  const comingText = $('#comingText');
  const closeComing = () => comingModal?.setAttribute('aria-hidden', 'true');
  $$('[data-coming-soon]').forEach((btn) => {
    btn.addEventListener('click', () => {
      if (!comingModal) return;
      if (comingTitle) comingTitle.textContent = btn.dataset.comingSoon || 'Em produção';
      if (comingText) comingText.textContent = btn.dataset.comingText || 'Estamos preparando esta experiência com cuidado.';
      comingModal.setAttribute('aria-hidden', 'false');
    });
  });
  $$('[data-close-coming]').forEach((btn) => btn.addEventListener('click', closeComing));

  // Custom confirm modal
  const confirmModal = $('#confirmModal');
  const confirmTitle = $('#confirmTitle');
  const confirmText = $('#confirmText');
  let confirmCallback = null;
  const closeConfirm = () => {
    confirmModal?.classList.remove('is-open');
    confirmModal?.setAttribute('aria-hidden', 'true');
    confirmCallback = null;
  };
  const openConfirm = (message, title, onConfirm) => {
    if (!confirmModal) {
      createToast('Não foi possível abrir a confirmação visual. Recarregue a página e tente novamente.', 'error');
      return;
    }
    if (confirmTitle) confirmTitle.textContent = title || 'Confirmar ação?';
    if (confirmText) confirmText.textContent = message || 'Esta ação precisa da sua confirmação.';
    confirmCallback = onConfirm;
    confirmModal.classList.add('is-open');
    confirmModal.setAttribute('aria-hidden', 'false');
  };
  $$('[data-confirm-cancel]').forEach((btn) => btn.addEventListener('click', closeConfirm));
  $('[data-confirm-ok]')?.addEventListener('click', () => {
    if (typeof confirmCallback === 'function') confirmCallback();
    closeConfirm();
  });
  $$('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      openConfirm(form.dataset.confirm, 'Confirmar ação?', () => form.submit());
    });
  });
  $$('[data-confirm-click]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openConfirm(btn.dataset.confirmClick, btn.dataset.confirmTitle || 'Confirmar ação?', () => {
        if (btn.dataset.href) window.location.href = btn.dataset.href;
      });
    });
  });

  // Typing reduzido: apenas elementos explicitamente marcados com data-typing.
  // O restante dos títulos usa transições/reveal suaves para manter a interface limpa.

  // Oracle deck: embaralha, revela e guarda o significado em um modal imersivo.
  const cards = [
    { number: '01', name: 'O Cavaleiro', symbol: '♞', theme: 'caminhos', keyword: 'movimento · notícia · impulso', meaning: 'Um chamado para agir e colocar energia em algo que está prestes a se mover. O Cavaleiro costuma falar de chegada, notícia e dinamismo.', reflection: 'O que já está pedindo movimento na sua vida?' },
    { number: '07', name: 'A Cobra', symbol: '∿', theme: 'ancora', keyword: 'atenção · estratégia · nuance', meaning: 'A Cobra fala de camadas, interesses ocultos e da necessidade de observar melhor antes de concluir. Nem tudo é o que parece na primeira leitura.', reflection: 'Onde vale desacelerar e perceber as entrelinhas?' },
    { number: '10', name: 'A Foice', symbol: '⌁', theme: 'sol', keyword: 'corte · decisão · clareza', meaning: 'Quando a Foice aparece, algo pede corte ou decisão objetiva. Às vezes, a paz começa no limite bem colocado.', reflection: 'O que precisa ser encerrado para abrir espaço ao essencial?' },
    { number: '13', name: 'A Criança', symbol: '◌', theme: 'trevo', keyword: 'começo · leveza · descoberta', meaning: 'A Criança aponta renovação, simplicidade e novas tentativas. Existe frescor quando você se permite recomeçar.', reflection: 'Que parte sua está pronta para experimentar de novo?' },
    { number: '15', name: 'O Urso', symbol: '◖', theme: 'chave', keyword: 'força · proteção · poder pessoal', meaning: 'O Urso fala de potência, proteção e presença. Pode ser um convite para ocupar melhor o próprio valor.', reflection: 'Onde você precisa confiar mais na própria força?' },
    { number: '18', name: 'O Cão', symbol: '✦', theme: 'estrela', keyword: 'aliança · lealdade · apoio', meaning: 'Carta ligada à confiança, às relações que sustentam e aos vínculos verdadeiros.', reflection: 'Quem caminha ao seu lado com sinceridade?' },
    { number: '24', name: 'O Coração', symbol: '♥', theme: 'coracao', keyword: 'afeto · desejo · verdade emocional', meaning: 'O Coração fala do que pulsa de verdade. Pode ser sobre afeto, mas também sobre aquilo que você ama construir e viver.', reflection: 'O que o seu coração está tentando dizer sem tanto ruído?' },
    { number: '28', name: 'O Homem', symbol: '⌂', theme: 'chave', keyword: 'presença · iniciativa · identidade', meaning: 'A carta aponta para posicionamento, responsabilidade e presença consciente dentro da situação.', reflection: 'Como você quer aparecer nessa história?' },
    { number: '30', name: 'Os Lírios', symbol: '❀', theme: 'sol', keyword: 'calma · maturidade · paz', meaning: 'Os Lírios pedem serenidade, delicadeza e uma forma mais madura de lidar com o momento.', reflection: 'O que muda quando você escolhe a paz sem passividade?' },
    { number: '32', name: 'A Lua', symbol: '☾', theme: 'lua', keyword: 'intuição · sensibilidade · ciclos', meaning: 'Convite para observar o que é sentido antes de ser explicado. A Lua fala de ciclos, percepção e daquilo que muda quando você olha com mais calma.', reflection: 'O que dentro de você já sabe a resposta, mesmo que ainda não consiga colocá-la em palavras?' },
    { number: '33', name: 'A Chave', symbol: '⌘', theme: 'chave', keyword: 'abertura · solução · passagem', meaning: 'A Chave aponta destravamento. Algo pode se abrir quando você enxerga a situação pelo ponto certo.', reflection: 'Qual porta se abre quando você para de insistir na entrada errada?' },
    { number: '36', name: 'A Cruz', symbol: '✢', theme: 'ancora', keyword: 'sentido · travessia · responsabilidade', meaning: 'A Cruz traz profundidade e aprendizado. Fala do peso que ensina e da travessia que transforma.', reflection: 'O que esse momento está tentando amadurecer em você?' }
  ];

  const stage = $('[data-oracle-deck]');
  if (stage) {
    const deck = $('.handmade-deck', stage) || stage;
    const trigger = $('[data-draw-card]', stage);
    const numberEl = $('[data-card-number]', stage);
    const nameEl = $('[data-card-name]', stage);
    const symbolEl = $('[data-card-symbol]', stage);
    const artEl = $('[data-card-art]', stage);
    const invite = $('[data-card-reveal-invite]');
    const inviteName = $('[data-card-invite-name]');
    const modal = $('#cardMeaningModal');
    const modalDialog = $('.card-meaning-dialog', modal || document);
    const modalNumber = $('[data-modal-card-number]', modal || document);
    const modalSymbol = $('[data-modal-card-symbol]', modal || document);
    const modalTitle = $('[data-modal-card-title]', modal || document);
    const modalKeyword = $('[data-modal-card-keyword]', modal || document);
    const modalMeaning = $('[data-modal-card-meaning]', modal || document);
    const modalReflection = $('[data-modal-card-reflection]', modal || document);
    let currentCard = cards.find((c) => c.number === '32') || cards[0];
    let shuffling = false;

    const applyCard = (card) => {
      currentCard = card;
      if (numberEl) numberEl.textContent = card.number;
      if (nameEl) nameEl.textContent = card.name;
      if (symbolEl) symbolEl.textContent = card.symbol;
      if (artEl) artEl.dataset.theme = card.theme;
      if (inviteName) inviteName.textContent = card.name;
      if (modalDialog) modalDialog.dataset.cardModalTheme = card.theme;
      if (modalNumber) modalNumber.textContent = card.number;
      if (modalSymbol) modalSymbol.textContent = card.symbol;
      if (modalTitle) modalTitle.textContent = card.name;
      if (modalKeyword) modalKeyword.textContent = card.keyword;
      if (modalMeaning) modalMeaning.textContent = card.meaning;
      if (modalReflection) modalReflection.textContent = card.reflection;
    };

    const closeMeaning = () => {
      if (!modal) return;
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('modal-open');
    };
    const openMeaning = () => {
      if (!modal) return;
      applyCard(currentCard);
      modal.classList.add('open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open');
      // O significado aparece inteiro no modal para leitura imediata; sem typing aqui.
    };
    $$('[data-close-card-meaning]').forEach((btn) => btn.addEventListener('click', closeMeaning));
    $('[data-open-card-meaning]')?.addEventListener('click', openMeaning);
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && modal?.classList.contains('open')) closeMeaning(); });

    trigger?.addEventListener('click', () => {
      if (shuffling) return;
      shuffling = true;
      if (invite) { invite.classList.remove('visible'); invite.hidden = true; }
      deck?.classList.remove('is-revealed');
      deck?.classList.add('is-shuffling');
      trigger.disabled = true;
      trigger.innerHTML = '<span>✦</span> Embaralhando...';

      setTimeout(() => {
        let next = currentCard;
        while (cards.length > 1 && next === currentCard) next = cards[Math.floor(Math.random() * cards.length)];
        applyCard(next);
        deck?.classList.remove('is-shuffling');
        deck?.classList.add('is-revealed');
        trigger.disabled = false;
        trigger.innerHTML = '<span>↻</span> Tirar outra carta';
        if (invite) {
          invite.hidden = false;
          requestAnimationFrame(() => invite.classList.add('visible'));
        }
        shuffling = false;
      }, reduceMotion ? 100 : 1450);
    });
  }

  // Selectable cards visual state
  $$('label.selectable-card input[type="checkbox"], label.selectable-card input[type="radio"]').forEach((input) => {
    const sync = () => input.closest('label')?.classList.toggle('is-selected', input.checked);
    sync();
    input.addEventListener('change', () => {
      if (input.type === 'radio' && input.name) {
        $$(`input[name="${CSS.escape(input.name)}"]`).forEach((peer) => peer.closest('label')?.classList.toggle('is-selected', peer.checked));
      }
      sync();
    });
  });

  $$('label.switch-card input[type="checkbox"]').forEach((input) => {
    const sync = () => input.closest('label')?.classList.toggle('is-on', input.checked);
    sync();
    input.addEventListener('change', sync);
  });

  // Editorial live preview in admin
  const setText = (selector, value, fallback = '') => {
    const el = $(selector);
    if (el) el.textContent = value || fallback;
  };
  const bodyField = $('[data-editorial="body"]');
  if (bodyField) {
    const updatePreview = () => {
      setText('#editorialType', $('[data-editorial="type"]')?.value, 'Blog');
      setText('#editorialCategory', $('[data-editorial="category"]')?.value, 'Estrada da Lua');
      const time = $('[data-editorial="time"]')?.value;
      setText('#editorialTime', time ? `${time} min` : '');
      setText('#editorialTitle', $('[data-editorial="title"]')?.value, 'Seu título começa aqui.');
      setText('#editorialSummary', $('[data-editorial="summary"]')?.value, 'O resumo aparece enquanto você escreve.');
      const body = bodyField.value.trim();
      setText('#editorialBody', body ? `${body.slice(0, 260)}${body.length > 260 ? '…' : ''}` : 'Um trecho do conteúdo aparece aqui para você sentir ritmo, respiro e hierarquia antes de publicar.');
      setText('#editorialCta', $('[data-editorial="cta"]')?.value, 'Continuar a experiência →');
      const counter = $('[data-editorial-count]');
      if (counter) counter.textContent = bodyField.value.length;
    };
    $$('[data-editorial]').forEach((field) => field.addEventListener('input', updatePreview));
    updatePreview();
  }

  // Image preview helper
  $$('[data-image-preview]').forEach((input) => {
    input.addEventListener('change', () => {
      const target = document.getElementById(input.dataset.imagePreview);
      const file = input.files?.[0];
      if (!target || !file) return;
      const reader = new FileReader();
      reader.onload = () => {
        target.innerHTML = `<img src="${reader.result}" alt="Prévia">`;
      };
      reader.readAsDataURL(file);
    });
  });
})();
