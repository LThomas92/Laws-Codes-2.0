<?php
/**
 * Global Search Component
 * template-parts/global-search.php
 *
 * HOW TO USE IN header.php:
 * ─────────────────────────
 * 1. Put the TRIGGER inside .site-nav__right (already in header.php):
 *      <?php get_template_part('template-parts/global-search'); ?>
 *
 *    This outputs only the button. The overlay is appended to <body>
 *    via wp_footer hook below — so it never sits inside the nav.
 *
 * 2. Make sure wp_footer() is called in footer.php. That's it.
 */

// Register the overlay to print at wp_footer — outside the nav entirely
add_action('wp_footer', 'lc_render_search_overlay', 5);

if ( ! function_exists('lc_render_search_overlay') ) :
function lc_render_search_overlay() {
  // Only render once even if partial is included multiple times
  static $rendered = false;
  if ($rendered) return;
  $rendered = true;

  $quick_links = [
    'Work'     => home_url('/work/'),
    'Services' => home_url('/services/'),
    'Process'  => home_url('/process/'),
    'About'    => home_url('/about/'),
    'Contact'  => home_url('/contact/'),
  ];

  $recent_cs = get_posts([
    'post_type'      => 'lc_case_study',
    'posts_per_page' => 3,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
  ]);
  ?>
  <!-- Global search overlay — rendered at wp_footer, outside <header> -->
  <div
    class="global-search-overlay"
    id="global-search-overlay"
    role="dialog"
    aria-label="Site search"
    aria-modal="true"
    aria-hidden="true"
  >
    <div class="global-search-overlay__backdrop" id="global-search-backdrop"></div>

    <div class="global-search-overlay__panel">

      <!-- Input row -->
      <div class="global-search-input-row">
        <svg class="global-search-input-row__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          class="global-search-input-row__input"
          id="global-search-input"
          type="search"
          placeholder="Search projects, pages, services…"
          autocomplete="off"
          aria-label="Search"
          aria-autocomplete="list"
          aria-controls="global-search-results"
          spellcheck="false"
        >
        <div class="global-search-input-row__kbd" aria-hidden="true">ESC</div>
        <button class="global-search-input-row__close" id="global-search-close" type="button" aria-label="Close search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- Results body -->
      <div class="global-search-body" id="global-search-results" role="listbox">

        <div class="global-search-state global-search-state--loading" id="gs-loading" hidden>
          <div class="global-search-spinner" aria-hidden="true"></div>
          <span>Searching…</span>
        </div>

        <div class="global-search-state global-search-state--empty" id="gs-empty" hidden>
          <span class="global-search-state__icon" aria-hidden="true">◎</span>
          <p>No results for "<strong id="gs-empty-term"></strong>"</p>
          <span>Try a different keyword or browse the site.</span>
        </div>

        <div id="gs-results-list"></div>

        <!-- Idle / default state -->
        <div class="global-search-default" id="gs-default">

          <div class="global-search-default__section">
            <span class="global-search-default__label">Quick links</span>
            <div class="global-search-default__links">
              <?php foreach ($quick_links as $label => $url) : ?>
              <a class="global-search-default__link" href="<?php echo esc_url($url); ?>">
                <span><?php echo esc_html($label); ?></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                  <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
              </a>
              <?php endforeach; ?>
            </div>
          </div>

          <?php if ($recent_cs) : ?>
          <div class="global-search-default__section">
            <span class="global-search-default__label">Recent work</span>
            <?php foreach ($recent_cs as $p) :
              $bg  = get_field('cs_bg_color', $p->ID) ?: '#0e0c2e';
              $ini = get_field('cs_initials',  $p->ID) ?: '';
              $ind = get_field('cs_industry',  $p->ID) ?: '';
            ?>
            <a class="gs-result-item" href="<?php echo esc_url(get_permalink($p->ID)); ?>">
              <div class="gs-result-item__thumb" style="background:<?php echo esc_attr($bg); ?>" aria-hidden="true">
                <?php echo esc_html($ini); ?>
              </div>
              <div class="gs-result-item__body">
                <div class="gs-result-item__title"><?php echo esc_html($p->post_title); ?></div>
                <div class="gs-result-item__meta"><?php echo esc_html($ind); ?></div>
              </div>
              <div class="gs-result-item__type" aria-hidden="true">Project</div>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- Keyboard hint bar -->
      <div class="global-search-footer" aria-hidden="true">
        <span><kbd>↑</kbd><kbd>↓</kbd> navigate</span>
        <span><kbd>↵</kbd> open</span>
        <span><kbd>ESC</kbd> close</span>
      </div>

    </div>
  </div>
  <?php
}
endif;

// Pass AJAX config to JS — safe to call here since we're inside get_template_part
// which runs during template rendering (after wp_enqueue_scripts has fired)
wp_localize_script('lawscodes-search', 'LC_SEARCH', [
  'ajaxUrl' => admin_url('admin-ajax.php'),
  'nonce'   => wp_create_nonce('lc_search_nonce'),
  'homeUrl' => home_url('/'),
]);
?>

<!-- Search trigger button — this IS inline in the nav -->
<button
  class="global-search-trigger"
  id="global-search-trigger"
  aria-label="Search site"
  aria-expanded="false"
  aria-controls="global-search-overlay"
  type="button"
>
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
  </svg>
  <span class="global-search-trigger__label">Search</span>
</button>