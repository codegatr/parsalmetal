/* ============================================================
   PARSAL METAL ALÜMINYUM - Ana JavaScript v1.0.0
   CODEGA
============================================================ */
'use strict';

/* ---------- HEADER SCROLL ---------- */
(function () {
  const hdr = document.getElementById('siteHeader');
  if (!hdr) return;
  const onScroll = () => hdr.classList.toggle('scrolled', window.scrollY > 40);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

/* ---------- HAMBURGER MENU ---------- */
(function () {
  const btn = document.getElementById('hamburger');
  const nav = document.getElementById('mobileNav');
  if (!btn || !nav) return;
  btn.addEventListener('click', () => {
    const open = btn.classList.toggle('open');
    nav.classList.toggle('open', open);
    btn.setAttribute('aria-expanded', open);
    document.body.style.overflow = open ? 'hidden' : '';
  });
  nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
    btn.classList.remove('open');
    nav.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }));
})();

/* ---------- HERO SLIDER ---------- */
(function () {
  const slider = document.querySelector('.hero-slider');
  if (!slider) return;
  const track  = slider.querySelector('.slider-track');
  const slides = slider.querySelectorAll('.slide');
  const dots   = slider.querySelectorAll('.slider-dot');
  const prev   = slider.querySelector('.slider-prev');
  const next   = slider.querySelector('.slider-next');
  const cntEl  = slider.querySelector('.slider-counter span');
  if (!slides.length) return;

  let current = 0, total = slides.length, timer = null, isAnimating = false;

  function goTo(idx) {
    if (isAnimating) return;
    isAnimating = true;
    slides[current].classList.remove('active');
    dots[current]?.classList.remove('active');
    current = (idx + total) % total;
    slides[current].classList.add('active');
    dots[current]?.classList.add('active');
    track.style.transform = `translateX(-${current * 100}%)`;
    if (cntEl) cntEl.textContent = current + 1;
    setTimeout(() => isAnimating = false, 750);
  }

  function startTimer() {
    clearInterval(timer);
    timer = setInterval(() => goTo(current + 1), 5500);
  }

  slides[0].classList.add('active');
  dots[0]?.classList.add('active');

  prev?.addEventListener('click', () => { goTo(current - 1); startTimer(); });
  next?.addEventListener('click', () => { goTo(current + 1); startTimer(); });
  dots.forEach((d, i) => d.addEventListener('click', () => { goTo(i); startTimer(); }));

  // Touch/Swipe
  let touchX = 0;
  slider.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
  slider.addEventListener('touchend', e => {
    const diff = touchX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) { goTo(diff > 0 ? current + 1 : current - 1); startTimer(); }
  });

  slider.addEventListener('mouseenter', () => clearInterval(timer));
  slider.addEventListener('mouseleave', startTimer);

  startTimer();
})();

/* ---------- COUNTER ANIMATION ---------- */
(function () {
  const items = document.querySelectorAll('.stat-value[data-target]');
  if (!items.length) return;
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el      = entry.target;
      const target  = parseFloat(el.dataset.target);
      const suffix  = el.dataset.suffix || '';
      const dur     = 2000;
      const step    = 16;
      const inc     = target / (dur / step);
      let current   = 0;
      const interval = setInterval(() => {
        current += inc;
        if (current >= target) { current = target; clearInterval(interval); }
        el.textContent = (Number.isInteger(target) ? Math.floor(current) : current.toFixed(1)) + suffix;
      }, step);
      obs.unobserve(el);
    });
  }, { threshold: 0.5 });
  items.forEach(el => obs.observe(el));
})();

/* ---------- PRODUCTS FILTER ---------- */
(function () {
  const btns  = document.querySelectorAll('.filter-btn');
  const cards = document.querySelectorAll('.product-card');
  if (!btns.length) return;
  btns.forEach(btn => btn.addEventListener('click', () => {
    btns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const cat = btn.dataset.cat;
    cards.forEach(card => {
      const show = cat === 'all' || card.dataset.cat === cat;
      card.style.display = show ? '' : 'none';
    });
  }));
})();

/* ---------- SCROLL ANIMATIONS ---------- */
(function () {
  const els = document.querySelectorAll('.service-card, .product-card, .why-card, .about-grid > *, .contact-card');
  if (!els.length || !window.IntersectionObserver) return;
  const obs = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        setTimeout(() => {
          e.target.style.opacity = '1';
          e.target.style.transform = 'translateY(0)';
        }, i * 80);
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  els.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    el.style.transition = 'opacity .5s ease, transform .5s ease';
    obs.observe(el);
  });
})();

/* ---------- COOKIE BAR ---------- */
(function () {
  const bar = document.querySelector('.cookie-bar');
  if (!bar) return;
  if (!localStorage.getItem('parsal_cookie')) bar.classList.add('show');
  document.querySelector('.cookie-accept')?.addEventListener('click', () => {
    localStorage.setItem('parsal_cookie', '1');
    bar.style.transform = 'translateY(100%)';
    setTimeout(() => bar.remove(), 400);
  });
  document.querySelector('.cookie-reject')?.addEventListener('click', () => {
    bar.style.transform = 'translateY(100%)';
    setTimeout(() => bar.remove(), 400);
  });
})();

/* ---------- FORM VALIDATION ---------- */
(function () {
  const form = document.querySelector('#quote-form, #contact-form');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    let valid = true;
    this.querySelectorAll('[required]').forEach(el => {
      el.classList.remove('input-error');
      if (!el.value.trim()) { el.classList.add('input-error'); valid = false; }
    });
    if (!valid) {
      e.preventDefault();
      const first = this.querySelector('.input-error');
      first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      first?.focus();
    }
  });
  // Real-time validation
  form.querySelectorAll('[required]').forEach(el => {
    el.addEventListener('input', () => {
      if (el.value.trim()) el.classList.remove('input-error');
    });
  });
})();

/* ---------- PHONE MASK ---------- */
(function () {
  document.querySelectorAll('input[type=tel]').forEach(el => {
    el.addEventListener('input', function () {
      let v = this.value.replace(/\D/g, '');
      if (v.startsWith('90')) v = '0' + v.slice(2);
      if (v.length > 11) v = v.slice(0, 11);
      this.value = v;
    });
  });
})();

/* ---------- SMOOTH SCROLL ---------- */
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  });
});

/* ---------- BACK TO TOP ---------- */
(function () {
  const btn = document.querySelector('.back-to-top');
  if (!btn) return;
  window.addEventListener('scroll', () => btn.classList.toggle('show', window.scrollY > 400), { passive: true });
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
})();

// iOS form zoom engelleme
(function () {
  if (!/iPad|iPhone|iPod/.test(navigator.userAgent)) return;
  document.querySelectorAll('input, select, textarea').forEach(function(el) {
    if (parseFloat(getComputedStyle(el).fontSize) < 16) el.style.fontSize = '16px';
  });
})();

// Orientasyon değişiminde yeniden çiz
window.addEventListener('orientationchange', function() {
  setTimeout(function() { window.dispatchEvent(new Event('resize')); }, 350);
});

// Touch cihazda :hover sorununu engelle
if ('ontouchstart' in window) {
  document.documentElement.classList.add('touch-device');
}
