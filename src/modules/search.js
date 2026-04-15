/**
 * global-search.js — Laws & Codes
 * Uses data-state on #gs-body to control which panel is visible.
 * Eliminates all hidden-attribute race conditions.
 */
(function () {
  'use strict';

  if (typeof LC_SEARCH === 'undefined') {
    console.warn('[LC Search] LC_SEARCH not found.');
    return;
  }

  const trigger     = document.getElementById('global-search-trigger');
  const overlay     = document.getElementById('global-search-overlay');
  const backdrop    = document.getElementById('global-search-backdrop');
  const closeBtn    = document.getElementById('global-search-close');
  const input       = document.getElementById('global-search-input');
  const body        = document.getElementById('gs-body');
  const resultsList = document.getElementById('gs-results-list');
  const emptyTermEl = document.getElementById('gs-empty-term');

  if (!trigger || !overlay || !input || !body) return;

  let debounce  = null;
  let current   = '';
  let selIdx    = -1;
  let abort     = null;

  // ── Single state setter ─────────────────────────────────────────────────────
  function setState(state, query) {
    body.dataset.state = state;
    if (state === 'empty' && emptyTermEl) {
      emptyTermEl.textContent = query || '';
    }
    if (state !== 'results') {
      resultsList.innerHTML = '';
      selIdx = -1;
    }
  }

  // ── Open / close ─────────────────────────────────────────────────────────────
  function open() {
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    trigger.setAttribute('aria-expanded', 'true');
    document.body.classList.add('search-open');
    setTimeout(() => input.focus(), 50);
  }

  function close() {
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
    trigger.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('search-open');
    input.value = '';
    current = '';
    selIdx  = -1;
    if (abort) { abort.abort(); abort = null; }
    setState('default');
    trigger.focus();
  }

  trigger.addEventListener('click', open);
  closeBtn.addEventListener('click', close);
  backdrop.addEventListener('click', close);

  document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault();
      overlay.classList.contains('is-open') ? close() : open();
    }
    if (e.key === 'Escape' && overlay.classList.contains('is-open')) close();
  });

  // ── Input ────────────────────────────────────────────────────────────────────
  input.addEventListener('input', () => {
    const val = input.value.trim();
    clearTimeout(debounce);
    if (val.length < 2) {
      if (abort) { abort.abort(); abort = null; }
      current = '';
      setState('default');
      return;
    }
    debounce = setTimeout(() => fetch(val), 260);
  });

  // ── Keyboard nav ─────────────────────────────────────────────────────────────
  input.addEventListener('keydown', e => {
    const items = resultsList.querySelectorAll('.gs-result-item');
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      selIdx = Math.min(selIdx + 1, items.length - 1);
      updateSel(items);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      selIdx = Math.max(selIdx - 1, -1);
      updateSel(items);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (selIdx >= 0 && items[selIdx]) {
        window.location.href = items[selIdx].dataset.url;
      } else if (input.value.trim()) {
        window.location.href = `${LC_SEARCH.homeUrl}?s=${encodeURIComponent(input.value.trim())}`;
      }
    }
  });

  function updateSel(items) {
    items.forEach((el, i) => {
      el.classList.toggle('is-selected', i === selIdx);
      if (i === selIdx) el.scrollIntoView({ block: 'nearest' });
    });
  }

  // ── Fetch ─────────────────────────────────────────────────────────────────────
  async function fetch(query) {
    if (query === current) return;
    current = query;
    selIdx  = -1;

    if (abort) abort.abort();
    abort = new AbortController();

    setState('loading');

    try {
      const params = new URLSearchParams({
        action: 'lc_global_search',
        nonce:  LC_SEARCH.nonce,
        q:      query,
      });

      const res  = await window.fetch(`${LC_SEARCH.ajaxUrl}?${params}`, { signal: abort.signal });
      const data = await res.json();

      if (query !== current) return; // stale

      if (!data.success || !data.data || data.data.total === 0) {
        setState('empty', query);
        return;
      }

      render(data.data.results, query);

    } catch (err) {
      if (err.name === 'AbortError') return;
      setState('empty', query);
    }
  }

  // ── Render ────────────────────────────────────────────────────────────────────
  function render(groups, query) {
    setState('results');

    groups.forEach(group => {
      if (!group.items || !group.items.length) return;

      const label = document.createElement('div');
      label.className   = 'gs-results-group-label';
      label.textContent = group.label;
      resultsList.appendChild(label);

      group.items.forEach(item => {
        const a = document.createElement('a');
        a.className   = 'gs-result-item';
        a.href        = item.url;
        a.dataset.url = item.url;
        a.setAttribute('role', 'option');

        if (item.type === 'project') {
          a.innerHTML = `
            <div class="gs-result-item__thumb" style="background:${esc(item.bg)}" aria-hidden="true">${esc(item.initials)}</div>
            <div class="gs-result-item__body">
              <div class="gs-result-item__title">${hi(item.title, query)}</div>
              <div class="gs-result-item__meta">${esc(item.meta)}</div>
            </div>
            <div class="gs-result-item__type" aria-hidden="true">Project</div>`;
        } else {
          const icons = {
            page:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
            post:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
            service: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
          };
          const typeLabel = item.type.charAt(0).toUpperCase() + item.type.slice(1);
          a.innerHTML = `
            <div class="gs-result-item__icon-wrap" aria-hidden="true">${icons[item.type] || icons.page}</div>
            <div class="gs-result-item__body">
              <div class="gs-result-item__title">${hi(item.title, query)}</div>
              <div class="gs-result-item__meta">${esc(item.meta)}</div>
            </div>
            <div class="gs-result-item__type" aria-hidden="true">${typeLabel}</div>`;
        }

        a.addEventListener('mousedown', e => { e.preventDefault(); window.location.href = item.url; });
        resultsList.appendChild(a);
      });
    });

    const viewAll = document.createElement('a');
    viewAll.className = 'gs-view-all';
    viewAll.href      = `${LC_SEARCH.homeUrl}?s=${encodeURIComponent(query)}`;
    viewAll.innerHTML = `View all results for &ldquo;<strong>${esc(query)}</strong>&rdquo; &rarr;`;
    resultsList.appendChild(viewAll);
  }

  function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function hi(text, q) {
    if (!q) return esc(text);
    return esc(text).replace(new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi'), '<mark>$1</mark>');
  }

})();