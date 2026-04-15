<?php
/**
 * Template Name: Work
 *
 * ACF Fields Required (ACF > Field Groups > "Work Page"):
 *
 * HERO GROUP (hero)
 *   - hero_kicker      (text)
 *   - hero_headline    (text)
 *   - hero_headline_em (text)   italic accent
 *   - hero_subtext     (textarea)
 *
 * FILTER LABELS (work_filters) — repeater
 *   - filter_label     (text)   e.g. "All", "WordPress", "E-Commerce"
 *   - filter_slug      (text)   e.g. "all", "wordpress", "ecommerce"
 *
 * CASE STUDIES — Custom Post Type "lc_case_study"
 *   - cs_industry / cs_location / cs_year / cs_description
 *   - cs_tags          comma-separated display tags
 *   - cs_kpi_number / cs_kpi_label
 *   - cs_bg_color / cs_initials
 *   - cs_filter_tags   comma-separated slugs (no spaces)
 *
 * TESTIMONIALS REPEATER (work_testimonials)
 *   - wt_quote / wt_name / wt_role / wt_initials
 */

get_header();

// Build search data for JS
$cs_search_data = [];
foreach ( get_posts(['post_type'=>'lc_case_study','posts_per_page'=>-1,'orderby'=>'menu_order','order'=>'ASC']) as $p ) {
  $cs_search_data[] = [
    'id'       => $p->ID,
    'title'    => $p->post_title,
    'industry' => (string)(get_field('cs_industry',$p->ID) ?: ''),
    'tags'     => (string)(get_field('cs_tags',$p->ID) ?: ''),
    'desc'     => (string)(get_field('cs_description',$p->ID) ?: ''),
    'bg'       => (string)(get_field('cs_bg_color',$p->ID) ?: '#0e0c2e'),
    'initials' => (string)(get_field('cs_initials',$p->ID) ?: ''),
    'url'      => get_permalink($p->ID),
    'filters'  => (string)(get_field('cs_filter_tags',$p->ID) ?: 'all'),
  ];
}

$hero    = get_field('hero');
$kicker  = $hero['hero_kicker']      ?? 'Selected work';
$hl      = $hero['hero_headline']    ?? 'Digital experiences built to last.';
$em_word = $hero['hero_headline_em'] ?? 'last.';
$sub     = $hero['hero_subtext']     ?? 'Every project is a partnership — built from scratch, no templates, no shortcuts.';
?>

<main class="lc-work" id="main">

  <!-- HERO -->
  <section class="work-hero">
    <div class="work-hero__inner">
      <div class="work-hero__tag">
        <span class="work-hero__dash"></span>
        <?php echo esc_html( $kicker ); ?>
      </div>
      <h1 class="work-hero__h1">
        <?php echo wp_kses_post( str_replace( esc_html($em_word), '<em>'.esc_html($em_word).'</em>', esc_html($hl) ) ); ?>
      </h1>
      <p class="work-hero__sub"><?php echo esc_html($sub); ?></p>
    </div>
  </section>

  <!-- TOOLBAR: filters + search -->
  <div class="work-toolbar">

    <div class="work-filters" role="group" aria-label="Filter projects">
      <?php
      $filters = get_field('work_filters');
      if ( $filters ) :
        foreach ( $filters as $i => $f ) : ?>
        <button
          type="button"
          class="work-filter<?php echo $i === 0 ? ' work-filter--active' : ''; ?>"
          data-filter="<?php echo esc_attr($f['filter_slug']); ?>"
          aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>"
        ><?php echo esc_html($f['filter_label']); ?></button>
        <?php endforeach;
      else : ?>
        <button type="button" class="work-filter work-filter--active" data-filter="all" aria-pressed="true">All</button>
        <button type="button" class="work-filter" data-filter="wordpress" aria-pressed="false">WordPress</button>
        <button type="button" class="work-filter" data-filter="ecommerce" aria-pressed="false">E-Commerce</button>
        <button type="button" class="work-filter" data-filter="branding" aria-pressed="false">Branding</button>
      <?php endif; ?>
    </div>

    <div class="work-search" id="work-search" role="search">
      <div class="work-search__wrap">
        <svg class="work-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          class="work-search__input"
          id="cs-search-input"
          type="search"
          placeholder="Search projects…"
          autocomplete="off"
          aria-label="Search projects"
          aria-expanded="false"
          aria-haspopup="listbox"
          aria-controls="cs-search-dropdown"
        >
        <button class="work-search__clear" id="cs-search-clear" type="button" aria-label="Clear search" hidden>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
      <div class="work-search__dropdown" id="cs-search-dropdown" role="listbox" hidden>
        <div class="work-search__results" id="cs-search-results"></div>
        <div class="work-search__no-results" id="cs-search-no-results" hidden>
          No projects match "<strong id="cs-search-term"></strong>"
        </div>
      </div>
    </div>

  </div>

  <!-- GRID -->
  <section class="work-grid section">

    <div class="work-search-banner" id="work-search-banner" hidden>
      Showing results for "<strong id="work-search-banner-term"></strong>"
      <button type="button" class="work-search-banner__clear" id="work-search-banner-clear">Clear ×</button>
    </div>

    <?php
    $cs_query = new WP_Query(['post_type'=>'lc_case_study','posts_per_page'=>-1,'orderby'=>'menu_order','order'=>'ASC']);
    ?>

    <?php if ( $cs_query->have_posts() ) : ?>
    <div class="cs-grid" id="cs-grid">
      <?php while ( $cs_query->have_posts() ) :
        $cs_query->the_post();
        $pid         = get_the_ID();
        $bg          = get_field('cs_bg_color')   ?: '#0e0c2e';
        $initials    = get_field('cs_initials')   ?: '';
        $tags_raw    = get_field('cs_tags')        ?: '';
        $tags        = array_filter(array_map('trim', explode(',', $tags_raw)));
        $kpi_n       = get_field('cs_kpi_number') ?: '';
        $kpi_l       = get_field('cs_kpi_label')  ?: '';
        $filter_tags = trim(get_field('cs_filter_tags') ?: 'all');
        $link        = get_permalink();
        $title       = get_the_title();
        $desc        = get_field('cs_description') ?: '';
        $data_tags   = 'all,' . str_replace(' ', '', $filter_tags);
      ?>
      <article
        class="cs-item"
        data-tags="<?php echo esc_attr($data_tags); ?>"
        data-title="<?php echo esc_attr(strtolower($title)); ?>"
        data-tags-text="<?php echo esc_attr(strtolower($tags_raw)); ?>"
        data-desc="<?php echo esc_attr(strtolower($desc)); ?>"
      >
        <a class="cs-item__link" href="<?php echo esc_url($link); ?>" aria-label="View <?php echo esc_attr($title); ?> case study">
          <div class="cs-thumb" style="background:<?php echo esc_attr($bg); ?>" aria-hidden="true">
            <?php echo esc_html($initials); ?>
          </div>
          <div class="cs-body">
            <div class="cs-tags" aria-hidden="true">
              <?php foreach ($tags as $t) : ?>
                <span class="cs-tag"><?php echo esc_html($t); ?></span>
              <?php endforeach; ?>
            </div>
            <div class="cs-title"><?php echo esc_html($title); ?></div>
            <div class="cs-desc"><?php echo esc_html($desc); ?></div>
          </div>
          <div class="cs-meta" aria-hidden="true">
            <?php if ($kpi_n) : ?>
            <div>
              <div class="cs-kpi-n"><?php echo esc_html($kpi_n); ?></div>
              <div class="cs-kpi-l"><?php echo esc_html($kpi_l); ?></div>
            </div>
            <?php endif; ?>
            <div class="cs-arr">→</div>
          </div>
        </a>
      </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <div class="cs-empty" id="cs-empty" hidden>
      <div class="cs-empty__icon">◎</div>
      <p class="cs-empty__text">No projects match that search.</p>
      <button type="button" class="cs-empty__reset" id="cs-empty-reset">Show all projects</button>
    </div>

    <?php else : ?>
    <p class="work-grid__empty">No case studies yet — check back soon.</p>
    <?php endif; ?>

  </section>

  <?php
  $testimonials = get_field('work_testimonials');
  if ($testimonials) : ?>
  <section class="work-testimonials section section--alt">
    <div class="section__header">
      <div class="section__kicker">Social proof</div>
      <h2 class="section__h">What clients say.</h2>
    </div>
    <div class="tgrid">
      <?php foreach ($testimonials as $t) : ?>
      <div class="tc">
        <div class="tc-stars"><?php for ($s=0;$s<5;$s++): ?><div class="star" aria-hidden="true"></div><?php endfor; ?></div>
        <blockquote class="tc-q">"<?php echo esc_html($t['wt_quote']); ?>"</blockquote>
        <div class="tc-person">
          <div class="tc-av"><?php echo esc_html($t['wt_initials']); ?></div>
          <div>
            <div class="tc-name"><?php echo esc_html($t['wt_name']); ?></div>
            <div class="tc-role"><?php echo esc_html($t['wt_role']); ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="lc-cta-section">
    <div class="lc-cta-section__kicker">Ready to begin</div>
    <h2 class="lc-cta-section__h">Let's build something<br>you're proud of.</h2>
    <p class="lc-cta-section__sub">No jargon. No hard sell. Just honest work.</p>
    <div class="lc-cta-section__slots">◆ Only 2 project slots open this quarter ◆</div>
    <a href="<?php echo esc_url(get_permalink(get_page_by_path('contact'))); ?>" class="lc-cta-section__btn">Book a discovery call</a>
  </section>

</main>

<script>
const LC_PROJECTS = <?php echo wp_json_encode($cs_search_data); ?>;

(function(){
  const filterBtns     = document.querySelectorAll('.work-filter');
  const csItems        = document.querySelectorAll('.cs-item');
  const csEmpty        = document.getElementById('cs-empty');
  const searchInput    = document.getElementById('cs-search-input');
  const searchClear    = document.getElementById('cs-search-clear');
  const searchDropdown = document.getElementById('cs-search-dropdown');
  const searchResults  = document.getElementById('cs-search-results');
  const searchNoRes    = document.getElementById('cs-search-no-results');
  const searchTermEl   = document.getElementById('cs-search-term');
  const banner         = document.getElementById('work-search-banner');
  const bannerTerm     = document.getElementById('work-search-banner-term');
  const bannerClear    = document.getElementById('work-search-banner-clear');
  const emptyReset     = document.getElementById('cs-empty-reset');

  let activeFilter  = 'all';
  let activeSearch  = '';
  let selectedIndex = -1;

  /* ── Apply filter + search ── */
  function applyState(){
    let count = 0;
    csItems.forEach(item => {
      const itemTags = item.dataset.tags.split(',');
      const filterOK = activeFilter === 'all' || itemTags.includes(activeFilter);
      const searchOK = activeSearch === '' ||
        (item.dataset.title    || '').includes(activeSearch) ||
        (item.dataset.desc     || '').includes(activeSearch) ||
        (item.dataset.tagsText || '').includes(activeSearch);
      const show = filterOK && searchOK;
      item.hidden = !show;
      if(show) count++;
    });
    if(csEmpty) csEmpty.hidden = count > 0;
  }

  /* ── Filter buttons ── */
  filterBtns.forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      activeFilter = btn.dataset.filter;
      filterBtns.forEach(b => { b.classList.remove('work-filter--active'); b.setAttribute('aria-pressed','false'); });
      btn.classList.add('work-filter--active');
      btn.setAttribute('aria-pressed','true');
      clearSearch();
      applyState();
    });
  });

  /* ── Empty reset ── */
  if(emptyReset){
    emptyReset.addEventListener('click', () => {
      activeFilter = 'all';
      filterBtns.forEach(b => { b.classList.remove('work-filter--active'); b.setAttribute('aria-pressed','false'); });
      const allBtn = document.querySelector('.work-filter[data-filter="all"]');
      if(allBtn){ allBtn.classList.add('work-filter--active'); allBtn.setAttribute('aria-pressed','true'); }
      clearSearch(); applyState();
    });
  }

  /* ── Search input ── */
  searchInput.addEventListener('input', () => {
    const val = searchInput.value.trim().toLowerCase();
    searchClear.hidden = val === '';
    if(val.length < 1){ closeDropdown(); activeSearch=''; applyState(); hideBanner(); return; }
    renderDropdown(val);
  });

  searchInput.addEventListener('keydown', e => {
    const items = searchResults.querySelectorAll('.cs-search-result');
    if(e.key==='ArrowDown'){ e.preventDefault(); selectedIndex=Math.min(selectedIndex+1,items.length-1); updateSel(items); }
    else if(e.key==='ArrowUp'){ e.preventDefault(); selectedIndex=Math.max(selectedIndex-1,-1); updateSel(items); }
    else if(e.key==='Enter'){
      e.preventDefault();
      if(selectedIndex>=0 && items[selectedIndex]) window.location=items[selectedIndex].dataset.url;
      else commitSearch(searchInput.value.trim());
    }
    else if(e.key==='Escape'){ closeDropdown(); searchInput.blur(); }
  });

  function updateSel(items){
    items.forEach((el,i) => { el.classList.toggle('is-selected', i===selectedIndex); if(i===selectedIndex) el.scrollIntoView({block:'nearest'}); });
  }

  searchClear.addEventListener('click', () => {
    searchInput.value=''; searchClear.hidden=true; activeSearch='';
    closeDropdown(); hideBanner(); applyState(); searchInput.focus();
  });

  if(bannerClear){
    bannerClear.addEventListener('click', () => {
      searchInput.value=''; searchClear.hidden=true; activeSearch='';
      hideBanner(); closeDropdown(); applyState();
    });
  }

  document.addEventListener('click', e => {
    if(!document.getElementById('work-search').contains(e.target)) closeDropdown();
  });

  /* ── Dropdown ── */
  function renderDropdown(query){
    selectedIndex = -1;
    const matches = LC_PROJECTS.filter(p =>
      p.title.toLowerCase().includes(query)    ||
      p.industry.toLowerCase().includes(query) ||
      p.tags.toLowerCase().includes(query)     ||
      p.desc.toLowerCase().includes(query)
    ).slice(0,6);

    searchResults.innerHTML = '';

    if(!matches.length){
      searchNoRes.hidden=false; searchResults.hidden=true;
      if(searchTermEl) searchTermEl.textContent=query;
    } else {
      searchNoRes.hidden=true; searchResults.hidden=false;
      matches.forEach(p => {
        const el = document.createElement('a');
        el.className='cs-search-result';
        el.href=p.url; el.dataset.url=p.url;
        el.setAttribute('role','option');
        el.innerHTML=`
          <div class="cs-search-result__thumb" style="background:${p.bg}" aria-hidden="true">${esc(p.initials)}</div>
          <div class="cs-search-result__body">
            <div class="cs-search-result__title">${highlight(p.title,query)}</div>
            <div class="cs-search-result__meta">${esc(p.industry)}</div>
          </div>
          <div class="cs-search-result__arrow" aria-hidden="true">→</div>`;
        el.addEventListener('mousedown', e => { e.preventDefault(); window.location=p.url; });
        searchResults.appendChild(el);
      });
    }
    openDropdown();
  }

  function commitSearch(query){
    if(!query) return;
    activeSearch=query.toLowerCase();
    closeDropdown(); showBanner(query); applyState();
  }

  function highlight(text,query){
    return esc(text).replace(new RegExp(`(${escRe(query)})`,'gi'),'<mark>$1</mark>');
  }
  function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
  function escRe(s){ return s.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'); }
  function openDropdown(){ searchDropdown.hidden=false; searchInput.setAttribute('aria-expanded','true'); }
  function closeDropdown(){ searchDropdown.hidden=true; searchInput.setAttribute('aria-expanded','false'); selectedIndex=-1; }
  function clearSearch(){ searchInput.value=''; searchClear.hidden=true; activeSearch=''; closeDropdown(); hideBanner(); }
  function showBanner(q){ if(!banner) return; banner.hidden=false; bannerTerm.textContent=q; }
  function hideBanner(){ if(!banner) return; banner.hidden=true; }

})();
</script>

<?php get_footer(); ?>