/**
 * global-search.js
 * Laws & Codes — Global site search with autocomplete overlay
 *
 * Enqueue in functions.php:
 *   wp_enqueue_script('lawscodes-search', get_template_directory_uri() . '/js/global-search.js', [], null, true);
 *
 * Requires LC_SEARCH global from wp_localize_script (set in global-search.php partial):
 *   LC_SEARCH.ajaxUrl
 *   LC_SEARCH.nonce
 */

(function () {
  'use strict';

  // ── DOM refs ────────────────────────────────────────────────────────────────
  const trigger     = document.getElementById('global-search-trigger');
  const overlay     = document.getElementById('global-search-overlay');
  const backdrop    = document.getElementById('global-search-backdrop');
  const closeBtn    = document.getElementById('global-search-close');
  const input       = document.getElementById('global-search-input');
  const resultsList = document.getElementById('gs-results-list');
  const loading     = document.getElementById('gs-loading');
  const emptyState  = document.getElementById('gs-empty');
  const emptyTerm   = document.getElementById('gs-empty-term');
  const defaultView = document.getElementById('gs-default');

  if (!trigger || !overlay || !input) return; // guard — partial not present

  let debounceTimer = null;
  let currentQuery  = '';
  let selectedIndex = -1;
  let abortCtrl     = null;

  // ── Open / Close ────────────────────────────────────────────────────────────
  function openSearch() {
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    trigger.setAttribute('aria-expanded', 'true');
    document.body.classList.add('search-open');
    // Small delay so transition completes before focus
    setTimeout(() => input.focus(), 50);
  }

  function closeSearch() {
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
    trigger.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('search-open');
    input.value = '';
    currentQuery = '';
    selectedIndex = -1;
    resetResults();
    trigger.focus();
  }

  trigger.addEventListener('click', openSearch);
  closeBtn.addEventListener('click', closeSearch);
  backdrop.addEventListener('click', closeSearch);

  // Keyboard shortcut: CMD+K / CTRL+K to open
  document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault();
      overlay.classList.contains('is-open') ? closeSearch() : openSearch();
    }
    if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
      closeSearch();
    }
  });

  // ── Input handler ───────────────────────────────────────────────────────────
  input.addEventListener('input', () => {
    const val = input.value.trim();

    if (val.length < 2) {
      clearTimeout(debounceTimer);
      if (abortCtrl) abortCtrl.abort();
      resetResults();
      currentQuery = '';
      return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchResults(val), 220);
  });

  // ── Keyboard navigation ─────────────────────────────────────────────────────
  input.addEventListener('keydown', e => {
    const items = resultsList.querySelectorAll('.gs-result-item[role="option"]');

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
      updateSelected(items);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      selectedIndex = Math.max(selectedIndex - 1, -1);
      updateSelected(items);
      if (selectedIndex === -1) input.focus();
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (selectedIndex >= 0 && items[selectedIndex]) {
        window.location.href = items[selectedIndex].dataset.url;
      } else if (input.value.trim()) {
        // Fall back to WordPress search results page
        window.location.href = `${LC_SEARCH.homeUrl}?s=${encodeURIComponent(input.value.trim())}`;
      }
    }
  });

  function updateSelected(items) {
    items.forEach((el, i) => {
      const active = i === selectedIndex;
      el.classList.toggle('is-selected', active);
      el.setAttribute('aria-selected', active ? 'true' : 'false');
      if (active) el.scrollIntoView({ block: 'nearest' });
    });
  }

  // ── Fetch ───────────────────────────────────────────────────────────────────
  async function fetchResults(query) {
    if (query === currentQuery) return;
    currentQuery = query;
    selectedIndex = -1;

    // Cancel in-flight request
    if (abortCtrl) abortCtrl.abort();
    abortCtrl = new AbortController();

    showLoading(true);
    hideEmpty();
    showDefault(false);
    resultsList.innerHTML = '';

    try {
      const params = new URLSearchParams({
        action: 'lc_global_search',
        nonce:  LC_SEARCH.nonce,
        q:      query,
      });

      const res  = await fetch(`${LC_SEARCH.ajaxUrl}?${params}`, { signal: abortCtrl.signal });
      const data = await res.json();

      showLoading(false);

      if (!data.success || !data.data.total) {
        showEmpty(query);
        return;
      }

      renderResults(data.data.results, query);

    } catch (err) {
      if (err.name !== 'AbortError') {
        showLoading(false);
        showEmpty(query);
      }
    }
  }

  // ── Render ──────────────────────────────────────────────────────────────────
  function renderResults(groups, query) {
    resultsList.innerHTML = '';
    selectedIndex = -1;

    groups.forEach(group => {
      if (!group.items || !group.items.length) return;

      // Group label
      const labelEl = document.createElement('div');
      labelEl.className   = 'gs-results-group-label';
      labelEl.textContent = group.label;
      resultsList.appendChild(labelEl);

      // Items
      group.items.forEach(item => {
        const a = document.createElement('a');
        a.className = 'gs-result-item';
        a.href      = item.url;
        a.dataset.url = item.url;
        a.setAttribute('role', 'option');
        a.setAttribute('aria-selected', 'false');

        if (item.type === 'project') {
          a.innerHTML = `
            <div class="gs-result-item__thumb" style="background:${esc(item.bg)}" aria-hidden="true">
              ${esc(item.initials)}
            </div>
            <div class="gs-result-item__body">
              <div class="gs-result-item__title">${highlight(item.title, query)}</div>
              <div class="gs-result-item__meta">${esc(item.meta)}</div>
            </div>
            <div class="gs-result-item__type" aria-hidden="true">Project</div>`;
        } else {
          const iconMap = {
            page:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
            post:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
            service: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
          };
          const icon = iconMap[item.type] || iconMap.page;
          a.innerHTML = `
            <div class="gs-result-item__icon-wrap" aria-hidden="true">${icon}</div>
            <div class="gs-result-item__body">
              <div class="gs-result-item__title">${highlight(item.title, query)}</div>
              <div class="gs-result-item__meta">${esc(item.meta)}</div>
            </div>
            <div class="gs-result-item__type" aria-hidden="true">${cap(item.type)}</div>`;
        }

        // Navigate on click
        a.addEventListener('mousedown', e => {
          e.preventDefault();
          window.location.href = item.url;
        });

        resultsList.appendChild(a);
      });
    });

    // "View all results" footer link
    const viewAll = document.createElement('a');
    viewAll.className = 'gs-view-all';
    viewAll.href      = `${LC_SEARCH.homeUrl}?s=${encodeURIComponent(query)}`;
    viewAll.innerHTML = `View all results for "<strong>${esc(query)}</strong>" →`;
    resultsList.appendChild(viewAll);
  }

  // ── State helpers ────────────────────────────────────────────────────────────
  function resetResults() {
    resultsList.innerHTML = '';
    showLoading(false);
    hideEmpty();
    showDefault(true);
    selectedIndex = -1;
  }

  function showLoading(show) {
    if (loading) loading.hidden = !show;
    if (show) { showDefault(false); hideEmpty(); }
  }

  function showEmpty(query) {
    if (emptyState) emptyState.hidden = false;
    if (emptyTerm)  emptyTerm.textContent = query;
    showDefault(false);
  }

  function hideEmpty() {
    if (emptyState) emptyState.hidden = true;
  }

  function showDefault(show) {
    if (defaultView) defaultView.hidden = !show;
  }

  // ── Utils ────────────────────────────────────────────────────────────────────
  function esc(s) {
    return String(s || '')
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function escRe(s) {
    return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }

  function highlight(text, query) {
    if (!query) return esc(text);
    return esc(text).replace(
      new RegExp(`(${escRe(query)})`, 'gi'),
      '<mark>$1</mark>'
    );
  }

  function cap(s) {
    return s.charAt(0).toUpperCase() + s.slice(1);
  }

})();