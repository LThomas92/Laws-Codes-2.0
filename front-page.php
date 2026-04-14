<?php
/**
 * Laws & Codes — front-page.php
 * Homepage template
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>

<!-- ── HERO ──────────────────────────────────────────── -->
<section class="hero">
  <?php $heroTag = get_field('hero_tag');
        $heroSubline = get_field('hero_subline');
        $heroButtonCTA = get_field('hero_button_cta_1');
        $heroButtonCTA2 = get_field('hero_button_cta_2');
  ?>
  <div class="hero-left">
    <div class="hero-eyebrow">
      <span class="hero-eyebrow-dash"></span>
      <span><?php echo $heroTag; ?></span>
    </div>
    <h1 class="hero-title">
      Make your<br>
      business<br>
      <em>impossible</em><br>
      to ignore.
    </h1>
    <p class="hero-sub">
      Custom websites, e-commerce, and digital experiences for businesses that
      refuse to blend in. Built from scratch — no templates.
    </p>
    <div class="hero-buttons">
      <a href="#work" class="btn-navy">View case studies</a>
      <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn-ghost">
        Book a discovery call
      </a>
    </div>
  </div>

  <div class="hero-right">
    <div>
      <div class="feat-label">Featured project</div>
      <div class="proj-switcher" id="lc-proj-switcher">
        <?php
        // Fallback static buttons (replaced by JS once LC_Projects loads)
        $featured = lc_get_featured_projects();
        foreach ( $featured as $i => $proj ) : ?>
          <button class="sw-btn<?php echo $i === 0 ? ' active' : ''; ?>" data-index="<?php echo $i; ?>">
            <?php echo esc_html( $proj['title'] ); ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <?php
    $first = $featured[0] ?? null;
    $bg    = $first['bgColor'] ?? '#0e0c2e';
    ?>
    <div class="featured-card">
      <div class="featured-card-img" id="lc-feat-img" style="background:<?php echo esc_attr( $bg ); ?>">
        <span class="featured-card-cat" id="lc-feat-cat">
          <?php echo esc_html( $first['categoryLabel'] ?? '' ); ?>
        </span>
      </div>
      <div class="featured-card-body">
        <div class="featured-card-title"   id="lc-feat-title">
          <?php echo esc_html( $first['title'] ?? '' ); ?>
        </div>
        <div class="featured-card-industry" id="lc-feat-industry">
          <?php echo esc_html( $first['industry'] ?? '' ); ?>
        </div>
        <div class="featured-card-kpis">
          <div class="feat-kpi">
            <div class="feat-kpi-number" id="lc-feat-kpi1-n"><?php echo esc_html( $first['kpi1Num']   ?? '' ); ?></div>
            <div class="feat-kpi-label"  id="lc-feat-kpi1-l"><?php echo esc_html( $first['kpi1Label'] ?? '' ); ?></div>
          </div>
          <div class="feat-kpi">
            <div class="feat-kpi-number" id="lc-feat-kpi2-n"><?php echo esc_html( $first['kpi2Num']   ?? '' ); ?></div>
            <div class="feat-kpi-label"  id="lc-feat-kpi2-l"><?php echo esc_html( $first['kpi2Label'] ?? '' ); ?></div>
          </div>
        </div>
        <div class="feat-pills" id="lc-feat-pills">
          <?php foreach ( ( $first['techStack'] ?? [] ) as $tech ) : ?>
            <span class="feat-pill"><?php echo esc_html( $tech ); ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <a href="<?php echo esc_url( $first['url'] ?? '#' ); ?>" id="lc-feat-link"
       class="btn-ghost" style="text-align:center;">
      View full case study →
    </a>
  </div>
</section>

<!-- ── MARQUEE ────────────────────────────────────────── -->
<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php
    $items = [
      'Custom WordPress', 'E-Commerce Development', 'Brand Identity',
      'SEO & Performance', 'Stripe Integration', 'React & JS',
      '6+ Years Experience', '14 Sites Delivered',
    ];
    // Duplicate for seamless loop
    $all = array_merge( $items, $items );
    foreach ( $all as $item ) : ?>
      <span class="marquee-item">
        <?php echo esc_html( $item ); ?>
        <span class="marquee-diamond">◆</span>
      </span>
    <?php endforeach; ?>
  </div>
</div>

<!-- ── STATS ──────────────────────────────────────────── -->
<div class="stats-strip">
  <div class="stat-block">
    <div class="stat-number">6<em>+</em></div>
    <div class="stat-label">Years experience</div>
  </div>
  <div class="stat-block">
    <div class="stat-number">14</div>
    <div class="stat-label">Sites delivered</div>
  </div>
  <div class="stat-block">
    <div class="stat-number">100<em>%</em></div>
    <div class="stat-label">Satisfaction rate</div>
  </div>
  <div class="stat-block">
    <div class="stat-number">5<em>★</em></div>
    <div class="stat-label">Client rating</div>
  </div>
</div>

<!-- ── CASE STUDIES ───────────────────────────────────── -->
<section class="projects-section section" id="work">
  <div class="section-header-row section-header">
    <div>
      <div class="section-kicker">Selected work</div>
      <h2 class="section-title">Case studies</h2>
    </div>
    <a href="<?php echo esc_url( get_post_type_archive_link( 'lc_project' ) ); ?>"
       class="view-all-btn">
      View all projects →
    </a>
  </div>

  <div class="cs-list">
    <?php
    $projects = new WP_Query([
      'post_type'      => 'lc_project',
      'posts_per_page' => 6,
      'orderby'        => 'menu_order',
      'order'          => 'ASC',
    ]);

    if ( $projects->have_posts() ) :
      while ( $projects->have_posts() ) :
        $projects->the_post();
        $id      = get_the_ID();
        $cat     = wp_get_post_terms( $id, 'lc_project_cat' );
        $bg      = get_post_meta( $id, '_lc_bg_color',      true ) ?: '#0e0c2e';
        $kpi_n   = get_post_meta( $id, '_lc_kpi_1_num',     true );
        $kpi_l   = get_post_meta( $id, '_lc_kpi_1_label',   true );
        $teaser  = get_post_meta( $id, '_lc_result_teaser', true );
        $initials = strtoupper( substr( get_the_title(), 0, 2 ) );
        ?>
        <a href="<?php the_permalink(); ?>" class="cs-item">
          <div class="cs-thumb" style="background:<?php echo esc_attr( $bg ); ?>">
            <?php echo esc_html( $initials ); ?>
          </div>
          <div class="cs-body">
            <?php if ( ! empty( $cat ) && ! is_wp_error( $cat ) ) : ?>
              <div class="cs-tags">
                <?php foreach ( $cat as $term ) : ?>
                  <span class="cs-tag"><?php echo esc_html( $term->name ); ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <div class="cs-title"><?php the_title(); ?></div>
            <?php if ( $teaser ) : ?>
              <div class="cs-description"><?php echo esc_html( $teaser ); ?></div>
            <?php endif; ?>
          </div>
          <div class="cs-meta">
            <?php if ( $kpi_n ) : ?>
              <div>
                <div class="cs-kpi-number"><?php echo esc_html( $kpi_n ); ?></div>
                <div class="cs-kpi-label"><?php echo esc_html( $kpi_l ); ?></div>
              </div>
            <?php endif; ?>
            <div class="cs-arrow">→</div>
          </div>
        </a>
      <?php endwhile;
      wp_reset_postdata();
    else : ?>
      <p style="padding:24px;color:#6b6b7e;">No projects yet — add some from the WordPress admin.</p>
    <?php endif; ?>
  </div>
</section>

<!-- ── QUIZ ───────────────────────────────────────────── -->
<?php get_template_part( 'template-parts/home/quiz' ); ?>

<!-- ── PROCESS ────────────────────────────────────────── -->
<section class="process-section">
  <div class="section-kicker">How we work</div>
  <h2 class="section-title">The process</h2>
  <p class="section-sub">Four focused phases — no hand-holding required.</p>
  <div class="process-grid" style="margin-top:36px">
    <?php
    $steps = [
      [ '01', 'Discovery', 'Brand deep-dive, competitor audit, goals alignment and kickoff workshop' ],
      [ '02', 'Design',    'Wireframes, high-fidelity mockups, iterated until every pixel is right'  ],
      [ '03', 'Build',     'Custom development, integrations, cross-device QA and performance testing' ],
      [ '04', 'Launch',    'Go live, team training, handoff, and 30 days of post-launch support'     ],
    ];
    foreach ( $steps as [ $n, $title, $desc ] ) : ?>
      <div class="process-card" data-step="<?php echo esc_attr( $n ); ?>">
        <div class="process-dot"></div>
        <div class="process-title"><?php echo esc_html( $title ); ?></div>
        <p class="process-desc"><?php echo esc_html( $desc ); ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── TESTIMONIALS ───────────────────────────────────── -->
<section class="testi-section">
  <div class="section-kicker">Social proof</div>
  <h2 class="section-title">What clients say</h2>
  <p class="section-sub">From businesses that trusted us with their most important digital asset.</p>
  <div class="testi-grid" style="margin-top:36px">
    <?php
    $testis = new WP_Query([
      'post_type'      => 'lc_testimonial',
      'posts_per_page' => 4,
      'orderby'        => 'menu_order',
      'order'          => 'ASC',
    ]);

    if ( $testis->have_posts() ) :
      while ( $testis->have_posts() ) :
        $testis->the_post();
        $id       = get_the_ID();
        $initials = get_post_meta( $id, '_lc_testi_initials', true );
        $name     = get_post_meta( $id, '_lc_testi_company',  true );
        $role     = get_post_meta( $id, '_lc_testi_role',     true );
        $stars    = (int) get_post_meta( $id, '_lc_testi_stars', true ) ?: 5;
        ?>
        <div class="testi-card">
          <div class="testi-stars">
            <?php for ( $s = 0; $s < $stars; $s++ ) : ?>
              <div class="testi-star"></div>
            <?php endfor; ?>
          </div>
          <blockquote class="testi-quote">
            "<?php echo wp_kses_post( get_the_content() ); ?>"
          </blockquote>
          <div class="testi-person">
            <div class="testi-avatar"><?php echo esc_html( $initials ); ?></div>
            <div>
              <div class="testi-name"><?php echo esc_html( $name ); ?></div>
              <div class="testi-role"><?php echo esc_html( $role ); ?></div>
            </div>
          </div>
        </div>
      <?php endwhile;
      wp_reset_postdata();
    else :
      // Fallback static testimonials while no CPT entries exist
      $fallbacks = [
        [ 'BB', 'The Brow Beast',  'Beauty &amp; Wellness · New York', '"Lawrence completely transformed how our business shows up online. The site drives real bookings every single day — it works like our best salesperson around the clock."' ],
        [ 'PB', 'Pearl Brewery',   'Hospitality · San Antonio, TX',   '"Our organic traffic nearly doubled in two months. The attention to both design and performance is something you rarely find in one person."' ],
        [ 'LC', 'Luceo',           'Professional Services',            '"More client inquiries in the first month after launch than in the entire previous year. The ROI has been undeniable."' ],
        [ 'PJ', 'Pati Jinich',     'Media &amp; Publishing',           '"I needed something that could handle recipes, media, and fan engagement in one place. Laws &amp; Codes delivered a site more beautiful than I imagined."' ],
      ];
      foreach ( $fallbacks as [ $init, $name, $role, $quote ] ) : ?>
        <div class="testi-card">
          <div class="testi-stars">
            <?php for ( $s = 0; $s < 5; $s++ ) : ?><div class="testi-star"></div><?php endfor; ?>
          </div>
          <blockquote class="testi-quote"><?php echo $quote; ?></blockquote>
          <div class="testi-person">
            <div class="testi-avatar"><?php echo esc_html( $init ); ?></div>
            <div>
              <div class="testi-name"><?php echo $name; ?></div>
              <div class="testi-role"><?php echo $role; ?></div>
            </div>
          </div>
        </div>
      <?php endforeach;
    endif; ?>
  </div>
</section>

<!-- ── CTA ────────────────────────────────────────────── -->
<section class="cta-section">
  <div class="cta-kicker">Ready to begin</div>
  <h2 class="cta-heading">Let's build something<br>you're proud of.</h2>
  <p class="cta-sub">
    No jargon. No hard sell. Just an honest conversation about your vision.<br>
    We respond within one business day.
  </p>
  <p class="cta-slots">◆ Only 2 project slots open this quarter ◆</p>
  <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="cta-button">
    Book a discovery call
  </a>
</section>

<?php get_footer(); ?>
