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
 * Register via functions.php. Each post has these ACF fields:
 *   - cs_industry      (text)
 *   - cs_location      (text)
 *   - cs_year          (text)
 *   - cs_description   (textarea)
 *   - cs_tags          (text)   comma-separated  e.g. "WordPress, WooCommerce, Stripe"
 *   - cs_kpi_number    (text)   e.g. "3×"
 *   - cs_kpi_label     (text)   e.g. "Booking growth"
 *   - cs_kpi2_number   (text)
 *   - cs_kpi2_label    (text)
 *   - cs_deliverables  (textarea)  comma-separated tech stack
 *   - cs_live_url      (url)
 *   - cs_github_url    (url)
 *   - cs_bg_color      (color_picker)   hex for thumbnail bg
 *   - cs_initials      (text)           e.g. "BB"
 *   - cs_challenge     (wysiwyg)
 *   - cs_what_we_built (wysiwyg)
 *   - cs_results       (wysiwyg)
 *   - cs_client        (text)
 *   - cs_timeline      (text)
 *   - cs_services_list (text)
 *   - cs_filter_tags   (text)  comma-separated slugs matching filter_slug above
 *
 * TESTIMONIALS REPEATER (work_testimonials)
 *   - wt_quote     (textarea)
 *   - wt_name      (text)
 *   - wt_role      (text)
 *   - wt_initials  (text)
 */

get_header(); ?>

<main class="lc-work" id="main">

  <?php
  $hero    = get_field('hero');
  $kicker  = $hero['hero_kicker']     ?? 'Selected work';
  $hl      = $hero['hero_headline']   ?? 'Digital experiences built to last.';
  $em_word = $hero['hero_headline_em'] ?? 'last.';
  $sub     = $hero['hero_subtext']    ?? 'Every project is a partnership — built from scratch, no templates, no shortcuts.';
  ?>

  <!-- HERO -->
  <section class="work-hero">
    <div class="work-hero__inner">
      <div class="work-hero__tag">
        <span class="work-hero__dash"></span>
        <?php echo esc_html( $kicker ); ?>
      </div>
      <h1 class="work-hero__h1">
        <?php echo wp_kses_post( str_replace( esc_html( $em_word ), '<em>' . esc_html( $em_word ) . '</em>', esc_html( $hl ) ) ); ?>
      </h1>
      <p class="work-hero__sub"><?php echo esc_html( $sub ); ?></p>
    </div>
  </section>

  <?php
  // ── Filters ───────────────────────────────────────────────────────────────
  $filters = get_field('work_filters');
  if ( $filters ) : ?>

  <div class="work-filters">
    <div class="work-filters__inner">
      <?php foreach ( $filters as $i => $f ) : ?>
      <button
        class="work-filter<?php echo $i === 0 ? ' work-filter--active' : ''; ?>"
        data-filter="<?php echo esc_attr( $f['filter_slug'] ); ?>"
      >
        <?php echo esc_html( $f['filter_label'] ); ?>
      </button>
      <?php endforeach; ?>
    </div>
  </div>

  <?php endif; ?>

  <?php
  // ── Case Studies Grid ─────────────────────────────────────────────────────
  $cs_args = [
    'post_type'      => 'lc_case_study',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
  ];
  $cs_query = new WP_Query( $cs_args );
  ?>

  <section class="work-grid section">
    <?php if ( $cs_query->have_posts() ) : ?>
    <div class="cs-grid" id="cs-grid">
      <?php while ( $cs_query->have_posts() ) :
        $cs_query->the_post();
        $id          = get_the_ID();
        $bg          = get_field('cs_bg_color')   ?: '#0e0c2e';
        $initials    = get_field('cs_initials')   ?: '';
        $industry    = get_field('cs_industry')   ?: '';
        $location    = get_field('cs_location')   ?: '';
        $tags_raw    = get_field('cs_tags')        ?: '';
        $tags        = array_map('trim', explode(',', $tags_raw));
        $kpi_n       = get_field('cs_kpi_number')  ?: '';
        $kpi_l       = get_field('cs_kpi_label')   ?: '';
        $filter_tags = get_field('cs_filter_tags') ?: 'all';
        $link        = get_permalink();
      ?>
      <article
        class="cs-item"
        data-tags="all,<?php echo esc_attr( $filter_tags ); ?>"
        onclick="window.location='<?php echo esc_url( $link ); ?>'"
        role="link"
        tabindex="0"
      >
        <div class="cs-thumb" style="background:<?php echo esc_attr( $bg ); ?>">
          <?php echo esc_html( $initials ); ?>
        </div>
        <div class="cs-body">
          <div class="cs-tags">
            <?php foreach ( $tags as $t ) : ?>
              <span class="cs-tag"><?php echo esc_html( $t ); ?></span>
            <?php endforeach; ?>
          </div>
          <div class="cs-title"><?php the_title(); ?></div>
          <div class="cs-desc"><?php echo esc_html( get_field('cs_description') ); ?></div>
        </div>
        <div class="cs-meta">
          <?php if ( $kpi_n ) : ?>
          <div>
            <div class="cs-kpi-n"><?php echo esc_html( $kpi_n ); ?></div>
            <div class="cs-kpi-l"><?php echo esc_html( $kpi_l ); ?></div>
          </div>
          <?php endif; ?>
          <div class="cs-arr" aria-hidden="true">→</div>
        </div>
      </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <?php else : ?>
    <p class="work-grid__empty">No case studies yet — check back soon.</p>
    <?php endif; ?>
  </section>

  <?php
  // ── Testimonials ──────────────────────────────────────────────────────────
  $testimonials = get_field('work_testimonials');
  if ( $testimonials ) : ?>

  <section class="work-testimonials section section--alt">
    <div class="section__header">
      <div class="section__kicker">Social proof</div>
      <h2 class="section__h">What clients say.</h2>
    </div>
    <div class="tgrid">
      <?php foreach ( $testimonials as $t ) : ?>
      <div class="tc">
        <div class="tc-stars">
          <?php for ( $s = 0; $s < 5; $s++ ) : ?><div class="star" aria-hidden="true"></div><?php endfor; ?>
        </div>
        <blockquote class="tc-q">"<?php echo esc_html( $t['wt_quote'] ); ?>"</blockquote>
        <div class="tc-person">
          <div class="tc-av"><?php echo esc_html( $t['wt_initials'] ); ?></div>
          <div>
            <div class="tc-name"><?php echo esc_html( $t['wt_name'] ); ?></div>
            <div class="tc-role"><?php echo esc_html( $t['wt_role'] ); ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php endif; ?>

  <!-- CTA -->
  <section class="lc-cta-section">
    <div class="lc-cta-section__kicker">Ready to begin</div>
    <h2 class="lc-cta-section__h">Let's build something<br>you're proud of.</h2>
    <p class="lc-cta-section__sub">No jargon. No hard sell. Just honest work.</p>
    <div class="lc-cta-section__slots">◆ Only 2 project slots open this quarter ◆</div>
    <a href="<?php echo esc_url( get_permalink( get_page_by_path('contact') ) ); ?>" class="lc-cta-section__btn">
      Book a discovery call
    </a>
  </section>

</main>

<script>
// Filter logic
const filterBtns = document.querySelectorAll('.work-filter');
const csItems    = document.querySelectorAll('.cs-item');

filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    const f = btn.dataset.filter;

    filterBtns.forEach(b => b.classList.remove('work-filter--active'));
    btn.classList.add('work-filter--active');

    csItems.forEach(item => {
      const tags = item.dataset.tags.split(',');
      item.style.display = tags.includes(f) ? '' : 'none';
    });
  });
});

// Keyboard nav on cs-item
document.querySelectorAll('.cs-item[role="link"]').forEach(el => {
  el.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') el.click();
  });
});
</script>

<?php get_footer(); ?>
