<?php
/**
 * Template Name: About
 *
 * ACF Fields Required (ACF > Field Groups > "About Page"):
 *
 * HERO GROUP (about_hero)
 *   - hero_kicker       (text)
 *   - hero_headline     (text)
 *   - hero_headline_em  (text)
 *   - hero_subtext      (textarea)
 *
 * FOUNDER GROUP (founder)
 *   - founder_name      (text)
 *   - founder_role      (text)
 *   - founder_bio_1     (textarea)  first paragraph
 *   - founder_bio_2     (textarea)  second paragraph
 *   - founder_image     (image)     returns array, use ['url']
 *   - founder_signature (image)     SVG / PNG cursive signature
 *
 * VALUES REPEATER (values_list)
 *   - value_number      (text)    e.g. "01"
 *   - value_title       (text)
 *   - value_description (textarea)
 *
 * STATS REPEATER (about_stats)
 *   - stat_number       (text)   e.g. "47+"
 *   - stat_label        (text)   e.g. "Projects delivered"
 *
 * SKILLS GROUP (skills_section)
 *   - skills_intro      (textarea)
 *   - skills_list       (textarea)  one skill per line
 *   - tools_list        (textarea)  one tool per line
 *
 * CTA GROUP (page_cta)  — same as services page
 */

get_header(); ?>

<main class="lc-about" id="main">

  <?php
  $hero    = get_field('about_hero');
  $kicker  = $hero['hero_kicker']     ?? 'The studio';
  $hl      = $hero['hero_headline']   ?? 'Code built with intention. Results you can measure.';
  $em_word = $hero['hero_headline_em'] ?? 'measure.';
  $sub     = $hero['hero_subtext']    ?? 'Laws & Codes is a boutique web studio based in New York. One developer. Full focus. No filler.';
  ?>

  <!-- HERO -->
  <section class="about-hero">
    <div class="about-hero__inner">
      <div class="about-hero__tag">
        <span class="about-hero__dash"></span>
        <?php echo esc_html( $kicker ); ?>
      </div>
      <h1 class="about-hero__h1">
        <?php echo wp_kses_post( str_replace( esc_html( $em_word ), '<em>' . esc_html( $em_word ) . '</em>', esc_html( $hl ) ) ); ?>
      </h1>
      <p class="about-hero__sub"><?php echo esc_html( $sub ); ?></p>
    </div>
  </section>

  <?php
  // ── STATS ─────────────────────────────────────────────────────────────────
  $stats = get_field('about_stats');
  if ( $stats ) : ?>

  <div class="about-stats">
    <?php foreach ( $stats as $stat ) : ?>
    <div class="about-stat">
      <div class="about-stat__n"><?php echo esc_html( $stat['stat_number'] ); ?></div>
      <div class="about-stat__l"><?php echo esc_html( $stat['stat_label'] ); ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>

  <?php
  // ── FOUNDER ───────────────────────────────────────────────────────────────
  $founder = get_field('founder');
  if ( $founder ) :
    $img = $founder['founder_image']['url'] ?? '';
    $sig = $founder['founder_signature']['url'] ?? '';
  ?>

  <section class="about-founder section">
    <div class="about-founder__grid">
      <div class="about-founder__copy">
        <div class="section__kicker">The person behind it</div>
        <h2 class="about-founder__name"><?php echo esc_html( $founder['founder_name'] ?? 'Lawrence' ); ?></h2>
        <div class="about-founder__role"><?php echo esc_html( $founder['founder_role'] ?? 'Founder & Developer' ); ?></div>
        <?php if ( $founder['founder_bio_1'] ) : ?>
          <p class="about-founder__bio"><?php echo esc_html( $founder['founder_bio_1'] ); ?></p>
        <?php endif; ?>
        <?php if ( $founder['founder_bio_2'] ) : ?>
          <p class="about-founder__bio"><?php echo esc_html( $founder['founder_bio_2'] ); ?></p>
        <?php endif; ?>
        <?php if ( $sig ) : ?>
          <img class="about-founder__sig" src="<?php echo esc_url( $sig ); ?>" alt="Signature" loading="lazy">
        <?php endif; ?>
      </div>
      <?php if ( $img ) : ?>
      <div class="about-founder__img-wrap">
        <img
          src="<?php echo esc_url( $img ); ?>"
          alt="<?php echo esc_attr( $founder['founder_name'] ?? 'Founder photo' ); ?>"
          class="about-founder__img"
          loading="lazy"
        >
        <div class="about-founder__img-frame" aria-hidden="true"></div>
      </div>
      <?php else : ?>
      <div class="about-founder__placeholder" aria-hidden="true">
        <span><?php echo esc_html( substr( $founder['founder_name'] ?? 'L', 0, 1 ) ); ?></span>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <?php endif; ?>

  <?php
  // ── VALUES ────────────────────────────────────────────────────────────────
  $values = get_field('values_list');
  if ( $values ) : ?>

  <section class="about-values section section--alt">
    <div class="section__header">
      <div class="section__kicker">What we stand for</div>
      <h2 class="section__h">Principles over shortcuts.</h2>
    </div>
    <div class="values-grid">
      <?php foreach ( $values as $v ) : ?>
      <div class="value-card" data-n="<?php echo esc_attr( $v['value_number'] ?? '' ); ?>">
        <div class="value-card__dot"></div>
        <h3 class="value-card__title"><?php echo esc_html( $v['value_title'] ); ?></h3>
        <p class="value-card__desc"><?php echo esc_html( $v['value_description'] ); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php endif; ?>

  <?php
  // ── SKILLS ────────────────────────────────────────────────────────────────
  $skills_group = get_field('skills_section');
  if ( $skills_group ) :
    $skills_intro = $skills_group['skills_intro'] ?? '';
    $skills_raw   = array_filter( array_map( 'trim', explode( "\n", $skills_group['skills_list'] ?? '' ) ) );
    $tools_raw    = array_filter( array_map( 'trim', explode( "\n", $skills_group['tools_list']  ?? '' ) ) );
  ?>

  <section class="about-skills section">
    <div class="about-skills__grid">
      <div>
        <div class="section__kicker">Craft</div>
        <h2 class="section__h">The skill set.</h2>
        <?php if ( $skills_intro ) : ?>
          <p class="section__sub"><?php echo esc_html( $skills_intro ); ?></p>
        <?php endif; ?>
      </div>
      <div class="about-skills__cols">
        <?php if ( $skills_raw ) : ?>
        <div class="about-skills__col">
          <div class="about-skills__col-label">Services</div>
          <ul class="about-skills__list">
            <?php foreach ( $skills_raw as $s ) : ?>
              <li><?php echo esc_html( $s ); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
        <?php if ( $tools_raw ) : ?>
        <div class="about-skills__col">
          <div class="about-skills__col-label">Tools & stack</div>
          <ul class="about-skills__list">
            <?php foreach ( $tools_raw as $t ) : ?>
              <li><?php echo esc_html( $t ); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php endif; ?>

  <!-- CTA -->
  <section class="lc-cta-section">
    <?php
    $cta       = get_field('page_cta');
    $cta_hl    = $cta['cta_headline']     ?? "Let's build something you're proud of.";
    $cta_sub   = $cta['cta_sub']          ?? 'No jargon. No hard sell. Just honest work.';
    $cta_label = $cta['cta_button_label'] ?? 'Book a discovery call';
    $cta_url   = $cta['cta_button_url']   ?? get_permalink( get_page_by_path('contact') );
    ?>
    <div class="lc-cta-section__kicker">Ready to begin</div>
    <h2 class="lc-cta-section__h"><?php echo wp_kses_post( $cta_hl ); ?></h2>
    <p class="lc-cta-section__sub"><?php echo esc_html( $cta_sub ); ?></p>
    <div class="lc-cta-section__slots">◆ Only 2 project slots open this quarter ◆</div>
    <a href="<?php echo esc_url( $cta_url ); ?>" class="lc-cta-section__btn">
      <?php echo esc_html( $cta_label ); ?>
    </a>
  </section>

</main>

<?php get_footer(); ?>
