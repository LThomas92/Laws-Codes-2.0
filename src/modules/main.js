/**
 * Laws & Codes — main.js
 * Nav, mobile menu, contact form, marquee pause
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {

    // ── MOBILE NAV ──────────────────────────────────────
    const hamburger = document.querySelector('.nav-hamburger');
    const mobileMenu = document.querySelector('.nav-mobile-menu');

    if (hamburger && mobileMenu) {
      hamburger.addEventListener('click', () => {
        const open = mobileMenu.classList.toggle('open');
        hamburger.setAttribute('aria-expanded', open);
      });
    }

    // ── CONTACT FORM ────────────────────────────────────
    const form = document.getElementById('lc-contact-form');
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = form.querySelector('.form-submit');
        const statusEl  = form.querySelector('.js-form-status');
        const original  = submitBtn.textContent;

        submitBtn.disabled   = true;
        submitBtn.textContent = 'Sending...';

        const fd = new FormData(form);
        fd.append('action', 'lc_contact');
        fd.append('nonce',  LC_Data.nonce);

        try {
          const res  = await fetch(LC_Data.ajaxUrl, { method: 'POST', body: fd });
          const data = await res.json();

          if (data.success) {
            form.reset();
            statusEl.textContent  = data.data.message;
            statusEl.className    = 'js-form-status form-status--success';
          } else {
            statusEl.textContent = data.data.message;
            statusEl.className   = 'js-form-status form-status--error';
          }
        } catch {
          statusEl.textContent = 'Something went wrong. Please try again.';
          statusEl.className   = 'js-form-status form-status--error';
        } finally {
          submitBtn.disabled    = false;
          submitBtn.textContent = original;
        }
      });
    }

    // ── SMOOTH SCROLL ────────────────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(link => {
      link.addEventListener('click', (e) => {
        const target = document.querySelector(link.getAttribute('href'));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });

  });

})();


/**
 * Laws & Codes — project-switcher.js
 * Featured project switcher on the homepage hero
 * Relies on LC_Projects localised from PHP
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const switcher = document.getElementById('lc-proj-switcher');
    if (!switcher || typeof LC_Projects === 'undefined' || !LC_Projects.length) return;

    const imgEl      = document.getElementById('lc-feat-img');
    const catEl      = document.getElementById('lc-feat-cat');
    const titleEl    = document.getElementById('lc-feat-title');
    const industryEl = document.getElementById('lc-feat-industry');
    const kpi1nEl    = document.getElementById('lc-feat-kpi1-n');
    const kpi1lEl    = document.getElementById('lc-feat-kpi1-l');
    const kpi2nEl    = document.getElementById('lc-feat-kpi2-n');
    const kpi2lEl    = document.getElementById('lc-feat-kpi2-l');
    const pillsEl    = document.getElementById('lc-feat-pills');
    const linkEl     = document.getElementById('lc-feat-link');

    function renderProject(p) {
      if (imgEl)      imgEl.style.background = p.bgColor || '#0e0c2e';
      if (catEl)      catEl.textContent       = p.categoryLabel || '';
      if (titleEl)    titleEl.textContent     = p.title || '';
      if (industryEl) industryEl.innerHTML    = p.industry || '';
      if (kpi1nEl)    kpi1nEl.textContent     = p.kpi1Num || '';
      if (kpi1lEl)    kpi1lEl.textContent     = p.kpi1Label || '';
      if (kpi2nEl)    kpi2nEl.textContent     = p.kpi2Num || '';
      if (kpi2lEl)    kpi2lEl.textContent     = p.kpi2Label || '';
      if (linkEl)     linkEl.href             = p.url || '#';

      if (pillsEl) {
        pillsEl.innerHTML = (p.techStack || [])
          .map(t => `<span class="feat-pill">${t}</span>`)
          .join('');
      }
    }

    // Build switcher buttons dynamically
    switcher.innerHTML = '';
    LC_Projects.forEach((project, i) => {
      const btn = document.createElement('button');
      btn.className   = 'sw-btn' + (i === 0 ? ' active' : '');
      btn.textContent = project.title;
      btn.dataset.index = i;

      btn.addEventListener('click', () => {
        switcher.querySelectorAll('.sw-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderProject(LC_Projects[i]);
      });

      switcher.appendChild(btn);
    });

    // Render first project
    if (LC_Projects[0]) renderProject(LC_Projects[0]);
  });

})();
