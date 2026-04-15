<?php
/**
 * Laws & Codes — front-page.php
 * Homepage template
 *
 * Case studies pulled from lc_case_study CPT.
 * Mark posts as featured via ACF field: cs_featured (true/false)
 * Control homepage order via ACF field: cs_switcher_order (number)
 */
defined( 'ABSPATH' ) || exit;

// ── Build featured case studies for the hero switcher ─────────────────────────
function lc_get_featured_case_studies(): array {
    $query = new WP_Query([
        'post_type'      => 'lc_case_study',
        'posts_per_page' => 6,
        'post_status'    => 'publish',
        'meta_query'     => [[
            'key'   => 'cs_featured',
            'value' => '1',
        ]],
        'meta_key' => 'cs_switcher_order',
        'orderby'  => 'meta_value_num',
        'order'    => 'ASC',
    ]);

    // Fallback: if none are marked featured, just show the first 4 by menu order
    if ( ! $query->have_posts() ) {
        $query = new WP_Query([
            'post_type'      => 'lc_case_study',
            'posts_per_page' => 4,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);
    }

    $projects = [];
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();
            // KPIs: try cs_kpis repeater first, fall back to flat fields
            $kpis_rep = get_field( 'cs_kpis', $id ) ?: [];
            $kpi1_num   = $kpis_rep[0]['kpi_number'] ?? get_field( 'cs_kpi_number',  $id ) ?? '';
            $kpi1_label = $kpis_rep[0]['kpi_label']  ?? get_field( 'cs_kpi_label',   $id ) ?? '';
            $kpi2_num   = $kpis_rep[1]['kpi_number'] ?? get_field( 'cs_kpi2_number', $id ) ?? '';
            $kpi2_label = $kpis_rep[1]['kpi_label']  ?? get_field( 'cs_kpi2_label',  $id ) ?? '';

            // Tech stack: try dedicated field, fall back to tags
            $stack_raw = get_field( 'cs_tech_stack', $id ) ?: get_field( 'cs_tags', $id ) ?: '';
            $stack     = array_filter( array_map( 'trim', explode( ',', (string) $stack_raw ) ) );

            $projects[] = [
                'id'            => $id,
                'title'         => get_the_title(),
                'url'           => get_permalink(),
                'industry'      => (string)( get_field( 'cs_industry',    $id ) ?: '' ),
                'location'      => (string)( get_field( 'cs_location',    $id ) ?: '' ),
                'categoryLabel' => (string)( get_field( 'cs_tags',        $id ) ?: '' ),
                'bgColor'       => (string)( get_field( 'cs_bg_color',    $id ) ?: '#0e0c2e' ),
                'initials'      => (string)( get_field( 'cs_initials',    $id ) ?: '' ),
                'kpi1Num'       => (string) $kpi1_num,
                'kpi1Label'     => (string) $kpi1_label,
                'kpi2Num'       => (string) $kpi2_num,
                'kpi2Label'     => (string) $kpi2_label,
                'techStack'     => array_values( $stack ),
                'description'   => (string)( get_field( 'cs_description', $id ) ?: '' ),
            ];
        }
        wp_reset_postdata();
    }

    return $projects;
}

$featured = lc_get_featured_case_studies();
$first    = $featured[0] ?? null;

get_header();
?>

<!-- ── HERO ───────────────────────────────────────────────────────────────── -->
<section class="hero">
  <?php
  $heroTag  = get_field('hero_tag');
  $heroSub  = get_field('hero_subline');
  $heroCTA1 = get_field('hero_button_cta_1');
  $heroCTA2 = get_field('hero_button_cta_2');
  ?>
  <div class="hero-left">
    <div class="hero-eyebrow">
      <span class="hero-eyebrow-dash"></span>
      <span><?php echo esc_html( $heroTag ); ?></span>
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
        <?php foreach ( $featured as $i => $proj ) : ?>
          <button class="sw-btn<?php echo $i === 0 ? ' active' : ''; ?>"
                  data-index="<?php echo esc_attr( $i ); ?>"
                  type="button">
            <?php echo esc_html( $proj['title'] ); ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <?php $bg = $first['bgColor'] ?? '#0e0c2e'; ?>
    <div class="featured-card">
      <div class="featured-card-img" id="lc-feat-img" style="background:<?php echo esc_attr( $bg ); ?>">
        <span class="featured-card-initials" id="lc-feat-initials" aria-hidden="true">
          <?php echo esc_html( $first['initials'] ?? '' ); ?>
        </span>
        <span class="featured-card-cat" id="lc-feat-cat">
          <?php
          // Show first tag as category label
          $first_tag = explode( ',', $first['categoryLabel'] ?? '' )[0] ?? '';
          echo esc_html( trim( $first_tag ) );
          ?>
        </span>
      </div>
      <div class="featured-card-body">
        <div class="featured-card-title" id="lc-feat-title">
          <?php echo esc_html( $first['title'] ?? '' ); ?>
        </div>
        <div class="featured-card-industry" id="lc-feat-industry">
          <?php
          $parts = array_filter( [ $first['industry'] ?? '', $first['location'] ?? '' ] );
          echo esc_html( implode( ' · ', $parts ) );
          ?>
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

    <a href="<?php echo esc_url( $first['url'] ?? home_url('/work/') ); ?>"
       id="lc-feat-link" class="btn-ghost" style="text-align:center;">
      View full case study &rarr;
    </a>
  </div>
</section>

<!-- ── MARQUEE ────────────────────────────────────────────────────────────── -->
<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php
    $items = [
      'Custom WordPress', 'E-Commerce Development', 'Brand Identity',
      'SEO & Performance', 'Stripe Integration', 'React & JS',
      '7+ Years Experience', '14 Sites Delivered',
    ];
    foreach ( array_merge( $items, $items ) as $item ) : ?>
      <span class="marquee-item">
        <?php echo esc_html( $item ); ?>
        <span class="marquee-diamond">&#9670;</span>
      </span>
    <?php endforeach; ?>
  </div>
</div>

<!-- ── STATS ──────────────────────────────────────────────────────────────── -->
<div class="stats-strip">
  <div class="stat-block">
    <div class="stat-number">7<em>+</em></div>
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
    <div class="stat-number">5<em>&#9733;</em></div>
    <div class="stat-label">Client rating</div>
  </div>
</div>

<!-- ── CASE STUDIES ───────────────────────────────────────────────────────── -->
<section class="projects-section section" id="work">
  <div class="section-header-row section-header">
    <div>
      <div class="section-kicker">Selected work</div>
      <h2 class="section-title">Case studies</h2>
    </div>
    <a href="<?php echo esc_url( home_url( '/work/' ) ); ?>" class="view-all-btn">
      View all projects &rarr;
    </a>
  </div>

  <?php
  // Show featured case studies on homepage, fall back to menu_order
  $hp_query = new WP_Query([
    'post_type'      => 'lc_case_study',
    'posts_per_page' => 6,
    'post_status'    => 'publish',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
  ]);
  ?>

  <div class="cs-grid cs-grid--home">
    <?php if ( $hp_query->have_posts() ) :
      while ( $hp_query->have_posts() ) :
        $hp_query->the_post();
        $id       = get_the_ID();
        $bg       = get_field( 'cs_bg_color',  $id ) ?: '#0e0c2e';
        $initials = get_field( 'cs_initials',  $id ) ?: strtoupper( substr( get_the_title(), 0, 2 ) );
        $industry = get_field( 'cs_industry',  $id ) ?: '';
        $location = get_field( 'cs_location',  $id ) ?: '';
        $tags_raw = get_field( 'cs_tags',      $id ) ?: '';
        $tags     = array_filter( array_map( 'trim', explode( ',', $tags_raw ) ) );
        $kpi_n    = get_field( 'cs_kpi_number', $id ) ?: '';
        $kpi_l    = get_field( 'cs_kpi_label',  $id ) ?: '';
        $desc     = get_field( 'cs_description', $id ) ?: '';
        $link     = get_permalink();
    ?>
    <article class="cs-item">
      <a class="cs-item__link" href="<?php echo esc_url( $link ); ?>"
         aria-label="View <?php echo esc_attr( get_the_title() ); ?> case study">

        <div class="cs-item__thumb" style="background:<?php echo esc_attr( $bg ); ?>">
          <span class="cs-item__initials" aria-hidden="true"><?php echo esc_html( $initials ); ?></span>
          <?php if ( $kpi_n ) : ?>
          <div class="cs-item__kpi">
            <span class="cs-item__kpi-n"><?php echo esc_html( $kpi_n ); ?></span>
            <span class="cs-item__kpi-l"><?php echo esc_html( $kpi_l ); ?></span>
          </div>
          <?php endif; ?>
        </div>

        <div class="cs-item__content">
          <div class="cs-item__meta-row">
            <?php foreach ( $tags as $t ) : ?>
              <span class="cs-item__tag"><?php echo esc_html( $t ); ?></span>
            <?php endforeach; ?>
          </div>
          <h3 class="cs-item__title"><?php the_title(); ?></h3>
          <?php if ( $industry || $location ) : ?>
          <p class="cs-item__industry">
            <?php echo esc_html( implode( ' · ', array_filter( [ $industry, $location ] ) ) ); ?>
          </p>
          <?php endif; ?>
          <?php if ( $desc ) : ?>
          <p class="cs-item__desc"><?php echo esc_html( $desc ); ?></p>
          <?php endif; ?>
        </div>

        <div class="cs-item__cta" aria-hidden="true">
          <span class="cs-item__cta-text">View project</span>
          <span class="cs-item__arrow">&#8594;</span>
        </div>

      </a>
    </article>
    <?php endwhile;
      wp_reset_postdata();
    else : ?>
      <p style="padding:24px;color:#6b6b7e;grid-column:1/-1;">
        No case studies yet — add some from WordPress admin &rarr; Case Studies.
      </p>
    <?php endif; ?>
  </div>
</section>

<!-- ── QUIZ ───────────────────────────────────────────────────────────────── -->
<?php get_template_part( 'template-parts/home/quiz' ); ?>

<!-- ── PROCESS ────────────────────────────────────────────────────────────── -->
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

<!-- ── TESTIMONIALS ───────────────────────────────────────────────────────── -->
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
          <blockquote class="testi-quote">"<?php echo wp_kses_post( get_the_content() ); ?>"</blockquote>
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
      // Static fallbacks while no lc_testimonial posts exist
      $fallbacks = [
        [ 'BB', 'The Brow Beast',  'Beauty &amp; Wellness · New York', '"Lawrence completely transformed how our business shows up online. The site drives real bookings every single day."' ],
        [ 'PB', 'Pearl Brewery',   'Hospitality · San Antonio, TX',   '"Our organic traffic nearly doubled in two months. The attention to both design and performance is something you rarely find in one person."' ],
        [ 'LC', 'Luceo Ventures',  'Professional Services',            '"More client inquiries in the first month after launch than in the entire previous year. The ROI has been undeniable."' ],
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

<!-- ── CTA ────────────────────────────────────────────────────────────────── -->
<section class="cta-section">
  <div class="cta-kicker">Ready to begin</div>
  <h2 class="cta-heading">Let's build something<br>you're proud of.</h2>
  <p class="cta-sub">
    No jargon. No hard sell. Just an honest conversation about your vision.<br>
    We respond within one business day.
  </p>
  <p class="cta-slots">&#9670; Only 2 project slots open this quarter &#9670;</p>
  <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="cta-button">
    Book a discovery call
  </a>
</section>

<!-- ── Hero switcher JS ───────────────────────────────────────────────────── -->
<script>
// Data printed inline — no timing issue with wp_footer
var LC_FeaturedProjects = <?php echo wp_json_encode( $featured ); ?>;
(function () {
  if (!LC_FeaturedProjects || !LC_FeaturedProjects.length) return;

  const btns      = document.querySelectorAll('.sw-btn');
  const imgEl     = document.getElementById('lc-feat-img');
  const initEl    = document.getElementById('lc-feat-initials');
  const catEl     = document.getElementById('lc-feat-cat');
  const titleEl   = document.getElementById('lc-feat-title');
  const industryEl= document.getElementById('lc-feat-industry');
  const kpi1n     = document.getElementById('lc-feat-kpi1-n');
  const kpi1l     = document.getElementById('lc-feat-kpi1-l');
  const kpi2n     = document.getElementById('lc-feat-kpi2-n');
  const kpi2l     = document.getElementById('lc-feat-kpi2-l');
  const pillsEl   = document.getElementById('lc-feat-pills');
  const linkEl    = document.getElementById('lc-feat-link');

  function switchTo(idx) {
    const p = LC_FeaturedProjects[idx];
    if (!p) return;

    // Active state
    btns.forEach((b, i) => b.classList.toggle('active', i === idx));

    // Update card
    if (imgEl)      imgEl.style.background = p.bgColor || '#0e0c2e';
    if (initEl)     initEl.textContent     = p.initials || '';
    if (catEl)      catEl.textContent      = (p.categoryLabel || '').split(',')[0].trim();
    if (titleEl)    titleEl.textContent    = p.title    || '';
    if (industryEl) {
      const parts = [p.industry, p.location].filter(Boolean);
      industryEl.textContent = parts.join(' · ');
    }
    if (kpi1n) kpi1n.textContent = p.kpi1Num   || '';
    if (kpi1l) kpi1l.textContent = p.kpi1Label || '';
    if (kpi2n) kpi2n.textContent = p.kpi2Num   || '';
    if (kpi2l) kpi2l.textContent = p.kpi2Label || '';

    if (pillsEl) {
      pillsEl.innerHTML = (p.techStack || [])
        .map(t => `<span class="feat-pill">${t.replace(/&/g,'&amp;').replace(/</g,'&lt;')}</span>`)
        .join('');
    }

    if (linkEl) linkEl.href = p.url || '#';
  }

  btns.forEach((btn, i) => {
    btn.addEventListener('click', () => switchTo(i));
  });

})();
</script>

<?php get_footer(); ?>