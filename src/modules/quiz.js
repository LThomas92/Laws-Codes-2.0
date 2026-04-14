/**
 * Laws & Codes — Quiz JS
 * Service recommendation quiz with weighted scoring
 */

(function () {
  'use strict';

  const ICONS = {
    globe:     '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
    refresh:   '<polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>',
    search:    '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
    box:       '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>',
    users:     '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
    cart:      '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
    award:     '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/>',
    trending:  '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
    scissors:  '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/>',
    tag:       '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
    briefcase: '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
    store:     '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
    sparkle:   '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
    check:     '<polyline points="20 6 9 17 4 12"/>',
    thumbsup:  '<path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>',
    zap:       '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
  };

  const QUESTIONS = [
    {
      q: 'What best describes your situation right now?',
      opts: [
        { icon: 'globe',   title: 'I have no website yet',                  desc: 'Starting from zero — I need a professional online presence',    score: { web: 3, ecomm: 1, seo: 0, brand: 1 } },
        { icon: 'refresh', title: 'My site is outdated or not converting',   desc: 'I have a site but it\'s not working hard enough for me',         score: { web: 3, ecomm: 1, seo: 1, brand: 0 } },
        { icon: 'search',  title: 'I exist online but can\'t be found',      desc: 'My site is decent but I\'m invisible on Google',                 score: { web: 0, ecomm: 0, seo: 3, brand: 0 } },
        { icon: 'box',     title: 'I want to sell products or services',     desc: 'I need a store with a real checkout experience',                 score: { web: 1, ecomm: 3, seo: 0, brand: 0 } },
      ],
    },
    {
      q: 'What\'s your primary goal for the next 6 months?',
      opts: [
        { icon: 'users',    title: 'Attract more customers',          desc: 'I want more people finding and trusting my business',           score: { web: 2, ecomm: 0, seo: 2, brand: 1 } },
        { icon: 'cart',     title: 'Start or grow online sales',      desc: 'I want to sell directly and generate real revenue online',      score: { web: 0, ecomm: 3, seo: 1, brand: 0 } },
        { icon: 'award',    title: 'Look better than my competitors', desc: 'My brand and site need to reflect the quality I deliver',      score: { web: 2, ecomm: 0, seo: 0, brand: 3 } },
        { icon: 'trending', title: 'Get found on Google',             desc: 'Ranking higher is my biggest bottleneck right now',            score: { web: 0, ecomm: 0, seo: 3, brand: 0 } },
      ],
    },
    {
      q: 'Which of these feels most like your business?',
      opts: [
        { icon: 'scissors',  title: 'Service-based business',   desc: 'Beauty, fitness, consulting, trades, healthcare, events', score: { web: 2, ecomm: 0, seo: 2, brand: 1 } },
        { icon: 'tag',       title: 'Product-based business',   desc: 'I make, sell, or source physical or digital products',   score: { web: 0, ecomm: 3, seo: 1, brand: 1 } },
        { icon: 'briefcase', title: 'Professional / B2B',        desc: 'Law, finance, tech, real estate, agencies',             score: { web: 2, ecomm: 0, seo: 1, brand: 2 } },
        { icon: 'store',     title: 'Restaurant or hospitality', desc: 'Brick-and-mortar with a local customer base',           score: { web: 1, ecomm: 1, seo: 3, brand: 1 } },
      ],
    },
    {
      q: 'Where does your brand stand today?',
      opts: [
        { icon: 'sparkle',  title: 'No logo or brand identity',          desc: 'I need a visual identity before anything else',              score: { web: 0, ecomm: 0, seo: 0, brand: 3 } },
        { icon: 'check',    title: 'I have a logo — it\'s just not great',desc: 'The basics are there but it feels off-brand',               score: { web: 1, ecomm: 0, seo: 0, brand: 2 } },
        { icon: 'thumbsup', title: 'Brand is solid, I need a better site',desc: 'Visuals are good — I need the platform to match',           score: { web: 3, ecomm: 1, seo: 1, brand: 0 } },
        { icon: 'zap',      title: 'Brand and site are good — I need growth', desc: 'Foundation is there, I need more visibility',          score: { web: 0, ecomm: 1, seo: 3, brand: 0 } },
      ],
    },
  ];

  const RESULTS = {
    web: {
      badge:    'Best match for you',
      service:  'Custom Website Design',
      desc:     'Your business needs a high-quality, conversion-focused website that reflects your brand and works hard every day. We build fully custom WordPress themes — no templates, no shortcuts.',
      includes: [
        'Fully custom responsive design',
        'Up to 8 pages (Home, About, Services, etc.)',
        'Contact form + Google Analytics setup',
        'On-page SEO foundation',
        '1 round of revisions included',
      ],
      price: 'Starting at $1,500',
    },
    ecomm: {
      badge:    'Best match for you',
      service:  'E-Commerce Development',
      desc:     "You're ready to sell online. We'll build a store on Shopify or WooCommerce with a seamless Stripe-powered checkout — designed to convert browsers into buyers.",
      includes: [
        'Shopify or WooCommerce platform setup',
        'Product catalog (up to 25 products)',
        'Stripe payment gateway integration',
        'Order management + confirmation emails',
        'Mobile-optimised checkout flow',
      ],
      price: 'Starting at $2,500',
    },
    seo: {
      badge:    'Best match for you',
      service:  'SEO & Ongoing Maintenance',
      desc:     "You have the foundation — now let's get you found. Our SEO package covers keyword strategy, on-page optimisation, monthly reporting, and proactive site maintenance.",
      includes: [
        'Keyword research & on-page optimisation',
        'Monthly performance report',
        'Plugin updates & security patches',
        'Content updates (up to 4 hrs/month)',
        'Google Search Console setup',
      ],
      price: 'From $300/month',
    },
    brand: {
      badge:    'Best match for you',
      service:  'Branding & Logo Design',
      desc:     "Before you can build the perfect website, you need a brand identity worth building around. We'll create a logo, colour system, typography guide, and a mini brand kit.",
      includes: [
        '3 initial logo concepts',
        'Brand colour palette + typography system',
        'PNG, SVG, and PDF file formats',
        'Mini brand style guide (1-page PDF)',
        'Unlimited revisions on chosen concept',
      ],
      price: 'Starting at $600',
    },
  };

  // ── DOM REFERENCES ──────────────────────────────────────
  const el = (id) => document.getElementById(id);

  class Quiz {
    constructor(containerId) {
      this.container = document.getElementById(containerId);
      if (!this.container) return;

      this.step    = 0;
      this.answers = [];
      this.scores  = { web: 0, ecomm: 0, seo: 0, brand: 0 };

      this.dom = {
        progressBar: this.container.querySelector('.quiz-progress-fill'),
        stepLabel:   this.container.querySelector('.quiz-step'),
        question:    this.container.querySelector('.quiz-question'),
        opts:        this.container.querySelector('.quiz-options'),
        body:        this.container.querySelector('.quiz-body-wrap'),
        footer:      this.container.querySelector('.quiz-footer'),
        dots:        this.container.querySelectorAll('.quiz-dot'),
        nextBtn:     this.container.querySelector('.js-quiz-next'),
        backBtn:     this.container.querySelector('.js-quiz-back'),
        result:      this.container.querySelector('.quiz-result'),
        rService:    this.container.querySelector('.result-service'),
        rDesc:       this.container.querySelector('.result-desc'),
        rIncludes:   this.container.querySelector('.js-result-includes'),
        rPrice:      this.container.querySelector('.result-price'),
        rCta:        this.container.querySelector('.js-result-cta'),
        retryBtn:    this.container.querySelector('.js-quiz-retry'),
      };

      this.dom.nextBtn.addEventListener('click', () => this.next());
      this.dom.backBtn.addEventListener('click', () => this.back());
      this.dom.retryBtn.addEventListener('click', () => this.reset());

      this.render();
    }

    render() {
      const q = QUESTIONS[this.step];
      const pct = Math.round((this.step / QUESTIONS.length) * 100);

      this.dom.progressBar.style.width = pct + '%';
      this.dom.stepLabel.textContent   = `Question ${this.step + 1} of ${QUESTIONS.length}`;
      this.dom.question.textContent    = q.q;
      this.dom.backBtn.style.display   = this.step > 0 ? 'inline-flex' : 'none';
      this.dom.nextBtn.disabled        = this.answers[this.step] === undefined;
      this.dom.nextBtn.textContent     = this.step === QUESTIONS.length - 1 ? 'See my result →' : 'Next →';

      // Update dots
      this.dom.dots.forEach((d, i) => {
        d.className = 'quiz-dot';
        if (i < this.step)       d.classList.add('done');
        else if (i === this.step) d.classList.add('active');
      });

      // Render options
      this.dom.opts.innerHTML = '';
      q.opts.forEach((opt, i) => {
        const btn = document.createElement('button');
        btn.className = 'quiz-option' + (this.answers[this.step] === i ? ' selected' : '');
        btn.innerHTML = `
          <div class="quiz-opt-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              ${ICONS[opt.icon] || ICONS.globe}
            </svg>
          </div>
          <div class="quiz-opt-text">
            <div class="quiz-opt-title">${opt.title}</div>
            <div class="quiz-opt-desc">${opt.desc}</div>
          </div>`;
        btn.addEventListener('click', () => this.select(i, btn));
        this.dom.opts.appendChild(btn);
      });
    }

    select(index, btn) {
      this.answers[this.step] = index;
      this.dom.opts.querySelectorAll('.quiz-option').forEach(b => b.classList.remove('selected'));
      btn.classList.add('selected');
      this.dom.nextBtn.disabled = false;
    }

    next() {
      if (this.answers[this.step] === undefined) return;

      // Accumulate scores
      const sc = QUESTIONS[this.step].opts[this.answers[this.step]].score;
      Object.keys(sc).forEach(k => { this.scores[k] += sc[k]; });

      if (this.step < QUESTIONS.length - 1) {
        this.step++;
        this.render();
      } else {
        this.showResult();
      }
    }

    back() {
      if (this.step <= 0) return;

      // Reverse the score for the previous answer
      const sc = QUESTIONS[this.step - 1].opts[this.answers[this.step - 1]].score;
      Object.keys(sc).forEach(k => { this.scores[k] -= sc[k]; });

      this.step--;
      this.render();
    }

    showResult() {
      this.dom.progressBar.style.width = '100%';
      this.dom.dots.forEach(d => d.className = 'quiz-dot done');

      // Determine winner
      const best = Object.keys(this.scores).reduce((a, b) =>
        this.scores[a] >= this.scores[b] ? a : b
      );
      const r = RESULTS[best];

      this.dom.rService.textContent  = r.service;
      this.dom.rDesc.textContent     = r.desc;
      this.dom.rPrice.textContent    = r.price;
      this.dom.rIncludes.innerHTML   = r.includes
        .map(item => `
          <div class="result-include-item">
            <div class="result-check">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                   stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>
            ${item}
          </div>`)
        .join('');

      // Contact page link
      if (typeof LC_Data !== 'undefined') {
        this.dom.rCta.href = LC_Data.siteUrl + '/contact/?service=' + encodeURIComponent(r.service);
      }

      // Log lead via AJAX
      if (typeof LC_Data !== 'undefined') {
        const fd = new FormData();
        fd.append('action',  'lc_quiz_lead');
        fd.append('nonce',   LC_Data.nonce);
        fd.append('result',  best);
        this.answers.forEach((a, i) => fd.append(`answers[${i}]`, a));
        fetch(LC_Data.ajaxUrl, { method: 'POST', body: fd });
      }

      this.dom.body.style.display   = 'none';
      this.dom.footer.style.display = 'none';
      this.dom.result.classList.add('visible');
    }

    reset() {
      this.step    = 0;
      this.answers = [];
      this.scores  = { web: 0, ecomm: 0, seo: 0, brand: 0 };

      this.dom.result.classList.remove('visible');
      this.dom.body.style.display   = 'block';
      this.dom.footer.style.display = 'flex';

      this.render();
    }
  }

  // Initialise when DOM is ready
  document.addEventListener('DOMContentLoaded', () => {
    new Quiz('lc-quiz');
  });

})();
