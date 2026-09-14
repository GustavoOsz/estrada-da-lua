(() => {
  const modal = document.getElementById('comingModal');
  const title = document.getElementById('comingTitle');
  const text = document.getElementById('comingText');
  const openComing = (el) => {
    if (!modal) return;
    title.textContent = el.dataset.comingSoon || 'Em produção';
    text.textContent = el.dataset.comingText || 'Estamos preparando esta experiência com o mesmo cuidado do restante da Estrada da Lua.';
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    modal.querySelector('.coming-card')?.focus?.();
  };
  const closeComing = () => {
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
  };
  document.querySelectorAll('[data-coming-soon]').forEach(el => el.addEventListener('click', () => openComing(el)));
  document.querySelectorAll('[data-close-coming]').forEach(el => el.addEventListener('click', closeComing));
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal?.classList.contains('open')) closeComing(); });

  const revealTargets = document.querySelectorAll('main > section, .product-card, .editorial-card, .oracle-service-card, .feedback-card, .process-card, .profile-order-card, .home-path-card, .conversation-item');
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    revealTargets.forEach((el, i) => { el.classList.add('reveal-v4'); el.style.setProperty('--reveal-delay', `${Math.min(i % 4, 3) * 65}ms`); });
    const io = new IntersectionObserver(entries => entries.forEach(entry => {
      if (entry.isIntersecting) { entry.target.classList.add('is-visible'); io.unobserve(entry.target); }
    }), {threshold: .07, rootMargin: '0px 0px -28px'});
    revealTargets.forEach(el => io.observe(el));
  } else {
    revealTargets.forEach(el => el.classList.add('is-visible'));
  }

  document.querySelectorAll('.product-card, .future-card, .ready-guide-card, .home-path-card').forEach(card => {
    card.addEventListener('pointermove', e => {
      const r = card.getBoundingClientRect();
      card.style.setProperty('--mx', `${e.clientX - r.left}px`);
      card.style.setProperty('--my', `${e.clientY - r.top}px`);
    });
  });
})();
