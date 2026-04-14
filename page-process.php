<?php
/**
 * Template Name: Process
 *
 * ACF Fields Required (ACF > Field Groups > "Process Page"):
 *
 * HERO GROUP (process_hero)
 *   - hero_kicker      (text)
 *   - hero_headline    (text)
 *   - hero_headline_em (text)
 *   - hero_subtext     (textarea)
 *
 * PHASES REPEATER (process_phases)
 *   - phase_number     (text)       "01"
 *   - phase_title      (text)       "Discovery"
 *   - phase_duration   (text)       "Week 1"
 *   - phase_summary    (textarea)
 *   - phase_deliverables (textarea) one per line
 *   - phase_tools      (text)       comma-separated
 *
 * EXPECTATIONS GROUP (expectations)
 *   - exp_intro        (textarea)
 *   - exp_items        (repeater)
 *     - exp_title      (text)
 *     - exp_body       (textarea)
 *
 * TIMELINE GROUP (timeline)
 *   - timeline_intro   (textarea)
 *   - typical_duration (text)       "4–8 weeks"
 *
 * CTA GROUP (page_cta) — same fields as other pages
 */

get_header(); ?>

<main class="lc-process" id="main">

  <?php
  $hero    = get_field('process_hero');
  $kicker  = $hero['hero_kicker']     ?? 'How we work';
  $hl      = $hero['hero_headline']   ?? 'Four phases. Zero hand-holding required.';
  $em_word = $hero['hero_headline_em'] ?? 'required.';
  $sub     = $hero['hero_subtext']    ?? 'A focused, repeatable process that takes you from idea to launch without the chaos.';
  ?>

  <!-- HERO -->
  <section class="proc-hero">
    <div class="proc-hero__inner">
      <div class="proc-hero__tag">
        <span class="proc-hero__dash"></span>
        <?php echo esc_html( $kicker ); ?>
      </div>
      <h1 class="proc-hero__h1">
        <?php echo wp_kses_post( str_replace( esc_html( $em_word ), '<em>' . esc_html( $em_word ) . '</em>', esc_html( $hl ) ) ); ?>
      </h1>
      <p class="proc-hero__sub"><?php echo esc_html( $sub ); ?></p>
    </div>
  </section>

  <?php
  // ── PHASES ────────────────────────────────────────────────────────────────
  $phases = get_field('process_phases');
  if ( $phases ) : ?>

  <section class="proc-phases section">
    <div class="section__header">
      <div class="section__kicker">The phases</div>
      <h2 class="section__h">How every project runs.</h2>
    </div>
    <div class="phases-list">
      <?php foreach ( $phases as $i => $phase ) :
        $deliverables = array_filter( array_map( 'trim', explode( "\n", $phase['phase_deliverables'] ?? '' ) ) );
        $tools        = array_map( 'trim', explode( ',', $phase['phase_tools'] ?? '' ) );
        $is_last      = ( $i === count( $phases ) - 1 );
      ?>
      <div class="phase-item<?php echo $is_last ? ' phase-item--last' : ''; ?>">
        <div class="phase-item__aside">
          <div class="phase-item__num"><?php echo esc_html( $phase['phase_number'] ); ?></div>
          <?php if ( ! $is_last ) : ?><div class="phase-item__connector" aria-hidden="true"></div><?php endif; ?>
        </div>
        <div class="phase-item__card">
          <div class="phase-item__header">
            <h3 class="phase-item__title"><?php echo esc_html( $phase['phase_title'] ); ?></h3>
            <?php if ( $phase['phase_duration'] ) : ?>
              <span class="phase-item__duration"><?php echo esc_html( $phase['phase_duration'] ); ?></span>
            <?php endif; ?>
          </div>
          <p class="phase-item__summary"><?php echo esc_html( $phase['phase_summary'] ); ?></p>
          <?php if ( ! empty( $deliverables ) ) : ?>
          <div class="phase-item__deliverables">
            <div class="phase-item__dl-label">Deliverables</div>
            <ul class="phase-item__dl-list">
              <?php foreach ( $deliverables as $d ) : ?>
                <li><?php echo esc_html( $d ); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>
          <?php if ( ! empty( $tools ) && $tools[0] !== '' ) : ?>
          <div class="phase-item__tools">
            <?php foreach ( $tools as $tool ) : ?>
              <span class="phase-item__tool"><?php echo esc_html( $tool ); ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php endif; ?>

  <?php
  // ── EXPECTATIONS ──────────────────────────────────────────────────────────
  $exp = get_field('expectations');
  if ( $exp ) : ?>

  <section class="proc-expectations section section--alt">
    <div class="section__header">
      <div class="section__kicker">Working together</div>
      <h2 class="section__h">What to expect.</h2>
      <?php if ( $exp['exp_intro'] ) : ?>
        <p class="section__sub"><?php echo esc_html( $exp['exp_intro'] ); ?></p>
      <?php endif; ?>
    </div>
    <?php if ( ! empty( $exp['exp_items'] ) ) : ?>
    <div class="exp-grid">
      <?php foreach ( $exp['exp_items'] as $item ) : ?>
      <div class="exp-card">
        <h3 class="exp-card__title"><?php echo esc_html( $item['exp_title'] ); ?></h3>
        <p class="exp-card__body"><?php echo esc_html( $item['exp_body'] ); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>

  <?php endif; ?>

  <?php
  // ── TIMELINE CALLOUT ──────────────────────────────────────────────────────
  $tl = get_field('timeline');
  if ( $tl ) : ?>

  <section class="proc-timeline section">
    <div class="timeline-callout">
      <div class="timeline-callout__left">
        <div class="section__kicker">Typical timeline</div>
        <div class="timeline-callout__duration"><?php echo esc_html( $tl['typical_duration'] ?? '4–8 weeks' ); ?></div>
        <div class="timeline-callout__label">From kickoff to launch</div>
      </div>
      <div class="timeline-callout__right">
        <p class="timeline-callout__intro"><?php echo esc_html( $tl['timeline_intro'] ?? '' ); ?></p>
        <a href="<?php echo esc_url( get_permalink( get_page_by_path('contact') ) ); ?>" class="btn-navy">
          Start your project →
        </a>
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
