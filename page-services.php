<?php
/**
 * Template Name: Services
 *
 * ACF Fields Required (add via ACF > Field Groups > "Services Page"):
 *
 * HERO GROUP (hero)
 *   - hero_kicker          (text)       e.g. "What we offer"
 *   - hero_headline        (text)       e.g. "Built for brands that mean business."
 *   - hero_headline_em     (text)       italic accent word(s)
 *   - hero_subtext         (textarea)
 *
 * SERVICES REPEATER (services_list)
 *   - service_number       (text)       e.g. "01"
 *   - service_title        (text)
 *   - service_description  (textarea)
 *   - service_deliverables (textarea)   comma-separated list
 *   - service_price_from   (text)       e.g. "From $2,400"
 *   - service_cta_label    (text)       e.g. "Start this service"
 *   - service_featured     (true_false) highlights card
 *
 * ADD-ONS REPEATER (addons_list)
 *   - addon_title          (text)
 *   - addon_price          (text)
 *   - addon_desc           (text)
 *
 * PACKAGES GROUP (packages)
 *   - packages_kicker      (text)
 *   - packages_headline    (text)
 *   - package_repeater     (repeater)
 *     - pkg_name           (text)
 *     - pkg_price          (text)
 *     - pkg_desc           (textarea)
 *     - pkg_features       (textarea)   one per line
 *     - pkg_cta            (text)
 *     - pkg_featured       (true_false)
 *
 * FAQ REPEATER (faq_list)
 *   - faq_question         (text)
 *   - faq_answer           (textarea)
 *
 * CTA GROUP (page_cta)
 *   - cta_headline         (text)
 *   - cta_sub              (textarea)
 *   - cta_button_label     (text)
 *   - cta_button_url       (url)
 */

get_header(); ?>

<main class="lc-services" id="main">

  <?php
  // ── HERO ─────────────────────────────────────────────────────────────────
  $hero = get_field('hero');
  $kicker    = $hero['hero_kicker']     ?? 'What we offer';
  $headline  = $hero['hero_headline']   ?? 'Built for brands that mean business.';
  $em_word   = $hero['hero_headline_em'] ?? 'business.';
  $subtext   = $hero['hero_subtext']    ?? '';
  ?>

  <section class="svc-hero">
    <div class="svc-hero__inner">
      <div class="svc-hero__tag">
        <span class="svc-hero__dash"></span>
        <?php echo esc_html( $kicker ); ?>
      </div>
      <h1 class="svc-hero__h1">
        <?php
          // Replace the em word in the headline
          echo wp_kses_post( str_replace(
            esc_html( $em_word ),
            '<em>' . esc_html( $em_word ) . '</em>',
            esc_html( $headline )
          ) );
        ?>
      </h1>
      <?php if ( $subtext ) : ?>
        <p class="svc-hero__sub"><?php echo esc_html( $subtext ); ?></p>
      <?php endif; ?>
    </div>
    <div class="svc-hero__scroll-cue" aria-hidden="true">
      <span class="svc-hero__scroll-line"></span>
    </div>
  </section>

  <?php
  // ── SERVICE CARDS ─────────────────────────────────────────────────────────
  $services = get_field('services_list');
  if ( $services ) : ?>

  <section class="svc-grid section">
    <div class="section__header">
      <div class="section__kicker">Core services</div>
      <h2 class="section__h">Everything your brand needs.</h2>
    </div>

    <div class="svc-cards">
      <?php foreach ( $services as $svc ) :
        $featured     = ! empty( $svc['service_featured'] );
        $deliverables = array_map( 'trim', explode( ',', $svc['service_deliverables'] ?? '' ) );
      ?>
      <article class="svc-card<?php echo $featured ? ' svc-card--featured' : ''; ?>">
        <div class="svc-card__num"><?php echo esc_html( $svc['service_number'] ?? '' ); ?></div>
        <div class="svc-card__body">
          <h3 class="svc-card__title"><?php echo esc_html( $svc['service_title'] ); ?></h3>
          <p class="svc-card__desc"><?php echo esc_html( $svc['service_description'] ); ?></p>
          <?php if ( ! empty( $deliverables ) && $deliverables[0] !== '' ) : ?>
          <ul class="svc-card__list">
            <?php foreach ( $deliverables as $item ) : ?>
              <li class="svc-card__item"><?php echo esc_html( $item ); ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
        <div class="svc-card__footer">
          <div class="svc-card__price"><?php echo esc_html( $svc['service_price_from'] ?? '' ); ?></div>
          <a href="<?php echo esc_url( get_permalink( get_page_by_path('contact') ) ); ?>" class="svc-card__cta">
            <?php echo esc_html( $svc['service_cta_label'] ?? 'Start project' ); ?> →
          </a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </section>

  <?php endif; ?>

  <?php
  // ── PACKAGES ─────────────────────────────────────────────────────────────
  $packages_group = get_field('packages');
  $pkg_repeater   = $packages_group['package_repeater'] ?? [];
  if ( ! empty( $pkg_repeater ) ) : ?>

  <section class="svc-packages section section--alt">
    <div class="section__header">
      <div class="section__kicker"><?php echo esc_html( $packages_group['packages_kicker'] ?? 'All-in packages' ); ?></div>
      <h2 class="section__h"><?php echo esc_html( $packages_group['packages_headline'] ?? 'Clear scope. Clear pricing.' ); ?></h2>
    </div>
    <div class="pkg-grid">
      <?php foreach ( $pkg_repeater as $pkg ) :
        $featured  = ! empty( $pkg['pkg_featured'] );
        $feat_list = array_filter( array_map( 'trim', explode( "\n", $pkg['pkg_features'] ?? '' ) ) );
      ?>
      <div class="pkg-card<?php echo $featured ? ' pkg-card--featured' : ''; ?>">
        <?php if ( $featured ) : ?><div class="pkg-card__badge">Most popular</div><?php endif; ?>
        <div class="pkg-card__name"><?php echo esc_html( $pkg['pkg_name'] ); ?></div>
        <div class="pkg-card__price"><?php echo esc_html( $pkg['pkg_price'] ); ?></div>
        <p class="pkg-card__desc"><?php echo esc_html( $pkg['pkg_desc'] ); ?></p>
        <?php if ( $feat_list ) : ?>
        <ul class="pkg-card__features">
          <?php foreach ( $feat_list as $f ) : ?>
            <li><?php echo esc_html( $f ); ?></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <a href="<?php echo esc_url( get_permalink( get_page_by_path('contact') ) ); ?>" class="pkg-card__btn">
          <?php echo esc_html( $pkg['pkg_cta'] ?? 'Get started' ); ?>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php endif; ?>

  <?php
  // ── ADD-ONS ───────────────────────────────────────────────────────────────
  $addons = get_field('addons_list');
  if ( $addons ) : ?>

  <section class="svc-addons section">
    <div class="section__header">
      <div class="section__kicker">Add-ons</div>
      <h2 class="section__h">Enhance your project.</h2>
    </div>
    <div class="addon-grid">
      <?php foreach ( $addons as $addon ) : ?>
      <div class="addon-card">
        <div class="addon-card__header">
          <span class="addon-card__title"><?php echo esc_html( $addon['addon_title'] ); ?></span>
          <span class="addon-card__price"><?php echo esc_html( $addon['addon_price'] ); ?></span>
        </div>
        <p class="addon-card__desc"><?php echo esc_html( $addon['addon_desc'] ); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php endif; ?>

  <?php
  // ── FAQ ───────────────────────────────────────────────────────────────────
  $faqs = get_field('faq_list');
  if ( $faqs ) : ?>

  <section class="svc-faq section section--alt">
    <div class="section__header">
      <div class="section__kicker">FAQ</div>
      <h2 class="section__h">Common questions.</h2>
    </div>
    <div class="faq-list" role="list">
      <?php foreach ( $faqs as $faq ) : ?>
      <div class="faq-item" role="listitem">
        <button class="faq-item__q" aria-expanded="false">
          <?php echo esc_html( $faq['faq_question'] ); ?>
          <span class="faq-item__icon" aria-hidden="true">+</span>
        </button>
        <div class="faq-item__a" hidden>
          <p><?php echo esc_html( $faq['faq_answer'] ); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php endif; ?>

  <?php
  // ── PAGE CTA ──────────────────────────────────────────────────────────────
  $cta         = get_field('page_cta');
  $cta_hl      = $cta['cta_headline']     ?? "Let's build something you're proud of.";
  $cta_sub     = $cta['cta_sub']          ?? 'No jargon. No hard sell. Just honest work.';
  $cta_label   = $cta['cta_button_label'] ?? 'Book a discovery call';
  $cta_url     = $cta['cta_button_url']   ?? get_permalink( get_page_by_path('contact') );
  ?>

  <section class="lc-cta-section">
    <div class="lc-cta-section__inner">
      <div class="lc-cta-section__kicker">Ready to begin</div>
      <h2 class="lc-cta-section__h"><?php echo wp_kses_post( $cta_hl ); ?></h2>
      <p class="lc-cta-section__sub"><?php echo esc_html( $cta_sub ); ?></p>
      <div class="lc-cta-section__slots">◆ Only 2 project slots open this quarter ◆</div>
      <a href="<?php echo esc_url( $cta_url ); ?>" class="lc-cta-section__btn">
        <?php echo esc_html( $cta_label ); ?>
      </a>
    </div>
  </section>

</main>

<script>
// FAQ accordion
document.querySelectorAll('.faq-item__q').forEach(btn => {
  btn.addEventListener('click', () => {
    const item   = btn.closest('.faq-item');
    const answer = item.querySelector('.faq-item__a');
    const open   = btn.getAttribute('aria-expanded') === 'true';
    // close all
    document.querySelectorAll('.faq-item__q').forEach(b => {
      b.setAttribute('aria-expanded', 'false');
      b.closest('.faq-item').querySelector('.faq-item__a').hidden = true;
      b.querySelector('.faq-item__icon').textContent = '+';
    });
    if ( ! open ) {
      btn.setAttribute('aria-expanded', 'true');
      answer.hidden = false;
      btn.querySelector('.faq-item__icon').textContent = '−';
    }
  });
});
</script>

<?php get_footer(); ?>
