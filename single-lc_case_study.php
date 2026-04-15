<?php
/**
 * Laws & Codes — single-lc_project.php
 * Individual project / case study template
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
  the_post();
  $id         = get_the_ID();
  $client     = get_post_meta( $id, 'cs_client_name',  true );
  $industry   = get_post_meta( $id, 'cs_industry',     true );
  $location   = get_post_meta( $id, 'cs_location',     true );
  $year       = get_post_meta( $id, 'cs_year',         true );
  $timeline   = get_post_meta( $id, 'cs_timeline',     true );
  $services   = get_post_meta( $id, 'cs_services',     true );
  $tech_raw   = get_post_meta( $id, 'cs_tech_stack',   true );
  $tech_arr   = $tech_raw ? array_map( 'trim', explode( ',', $tech_raw ) ) : [];
  $kpis       = [
    [ get_post_meta( $id, 'cs_kpi_1_num', true ), get_post_meta( $id, 'cs_kpi_1_label', true ) ],
    [ get_post_meta( $id, 'cs_kpi_2_num', true ), get_post_meta( $id, 'cs_kpi_2_label', true ) ],
    [ get_post_meta( $id, 'cs_kpi_3_num', true ), get_post_meta( $id, 'cs_kpi_3_label', true ) ],
    [ get_post_meta( $id, 'cs_kpi_4_num', true ), get_post_meta( $id, 'cs_kpi_4_label', true ) ],
  ];
  $github     = get_post_meta( $id, 'cs_github_url',   true );
  $live       = get_post_meta( $id, 'cs_live_url',     true );
  $challenge  = get_post_meta( $id, 'cs_challenge',    true );
  $solution   = get_post_meta( $id, 'cs_solution',     true );
  $results    = get_post_meta( $id, 'cs_results',      true );
  $bg         = get_post_meta( $id, 'cs_bg_color',     true ) ?: '#0e0c2e';
  $cat_label  = get_post_meta( $id, 'cs_category_label', true );
?>

<!-- ── PROJECT HERO ─────────────────────────────────── -->
<div class="project-hero">
  <a href="<?php echo esc_url( get_permalink( get_page_by_path('work') ) ); ?>"
     class="project-back-btn">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    All case studies
  </a>

  <div class="project-hero-grid">
    <div>
      <div class="project-kicker">
        <?php echo esc_html( implode( ' · ', array_filter( [ $cat_label ?: $industry, $location, $year ] ) ) ); ?>
      </div>

      <h1 class="project-title">
        <?php
        $words = explode( ' ', get_the_title() );
        $last  = array_pop( $words );
        if ( $words ) {
          echo esc_html( implode( ' ', $words ) ) . '<br><em>' . esc_html( $last ) . '.</em>';
        } else {
          echo '<em>' . esc_html( $last ) . '.</em>';
        }
        ?>
      </h1>

      <p class="project-description"><?php echo esc_html( get_the_excerpt() ); ?></p>

      <?php if ( $tech_arr ) : ?>
        <div class="project-tech-pills">
          <?php foreach ( $tech_arr as $t ) : ?>
            <span class="tech-pill"><?php echo esc_html( $t ); ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Browser mockup -->
    <div class="browser-mockup">
      <div class="browser-bar">
        <div class="browser-dot browser-dot-r"></div>
        <div class="browser-dot browser-dot-y"></div>
        <div class="browser-dot browser-dot-g"></div>
        <?php if ( $live ) : ?>
          <div class="browser-url"><?php echo esc_html( preg_replace( '#^https?://#', '', $live ) ); ?></div>
        <?php endif; ?>
      </div>
      <div class="browser-screen">
        <?php if ( has_post_thumbnail() ) : ?>
          <?php the_post_thumbnail( 'lc-project-hero', [ 'alt' => get_the_title() . ' website screenshot', 'style' => 'width:100%;height:100%;object-fit:cover;' ] ); ?>
        <?php else : ?>
          <div style="background:<?php echo esc_attr( $bg ); ?>;height:100%;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.3);font-family:serif;font-size:13px;">
            <?php echo esc_html( get_the_title() ); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ── METRICS ──────────────────────────────────────── -->
<?php if ( array_filter( array_column( $kpis, 0 ) ) ) : ?>
  <div class="project-metrics">
    <?php foreach ( $kpis as [ $num, $label ] ) :
      if ( ! $num ) continue; ?>
      <div class="project-metric">
        <div class="metric-number"><?php echo esc_html( $num ); ?></div>
        <div class="metric-label"><?php echo esc_html( $label ); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- ── BODY ─────────────────────────────────────────── -->
<div class="project-body">
  <div class="project-main-content">

    <?php if ( $challenge ) : ?>
      <h2 class="project-section-title">The challenge</h2>
      <div class="project-text"><?php echo wp_kses_post( $challenge ); ?></div>
    <?php endif; ?>

    <?php if ( $solution ) : ?>
      <h2 class="project-section-title">What we built</h2>
      <div class="project-text"><?php echo wp_kses_post( $solution ); ?></div>
    <?php endif; ?>

    <?php if ( $results ) : ?>
      <h2 class="project-section-title">Results</h2>
      <div class="project-text"><?php echo wp_kses_post( $results ); ?></div>
    <?php endif; ?>

    <?php if ( ! $challenge && ! $solution ) : ?>
      <div class="project-text"><?php the_content(); ?></div>
    <?php endif; ?>
  </div>

  <!-- SIDEBAR -->
  <aside class="project-sidebar">
    <div class="project-sidebar-box">
      <div class="sidebar-title">Project details</div>
      <?php
      $details = array_filter([
        'Client'   => $client   ?: get_the_title(),
        'Industry' => $industry,
        'Location' => $location,
        'Year'     => $year,
        'Timeline' => $timeline,
        'Services' => $services,
      ]);
      foreach ( $details as $k => $v ) : ?>
        <div class="sidebar-row">
          <span class="sidebar-key"><?php echo esc_html( $k ); ?></span>
          <span class="sidebar-value"><?php echo esc_html( $v ); ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ( $github || $live ) : ?>
      <div class="project-sidebar-box">
        <div class="sidebar-title">Links</div>
        <?php if ( $github ) : ?>
          <a href="<?php echo esc_url( $github ); ?>" class="project-link-btn project-link-btn--github"
             target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.44 9.8 8.2 11.4.6.1.82-.26.82-.57v-2c-3.34.72-4.04-1.6-4.04-1.6-.54-1.4-1.33-1.76-1.33-1.76-1.08-.74.08-.72.08-.72 1.2.08 1.83 1.23 1.83 1.23 1.06 1.82 2.8 1.3 3.48.99.1-.77.42-1.3.76-1.6-2.67-.3-5.47-1.33-5.47-5.93 0-1.3.47-2.38 1.24-3.22-.12-.3-.54-1.52.12-3.18 0 0 1-.32 3.3 1.23a11.5 11.5 0 0 1 6 0c2.28-1.55 3.3-1.23 3.3-1.23.66 1.66.24 2.88.12 3.18.77.84 1.23 1.92 1.23 3.22 0 4.61-2.8 5.63-5.48 5.92.43.37.81 1.1.81 2.22v3.3c0 .32.22.68.82.56C20.56 21.8 24 17.3 24 12c0-6.63-5.37-12-12-12z"/>
            </svg>
            View on GitHub
          </a>
        <?php endif; ?>
        <?php if ( $live ) : ?>
          <a href="<?php echo esc_url( $live ); ?>" class="project-link-btn project-link-btn--live"
             target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
              <polyline points="15 3 21 3 21 9"/>
              <line x1="10" y1="14" x2="21" y2="3"/>
            </svg>
            Visit live site
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </aside>
</div>

<!-- ── NEXT PROJECT ─────────────────────────────────── -->
<?php
$next = get_adjacent_post( false, '', false );
if ( $next ) : ?>
  <div style="border-top:1px solid rgba(7,9,69,0.11);padding:32px 48px;display:flex;justify-content:flex-end;background:#f9f8f5">
    <a href="<?php echo esc_url( get_permalink( $next ) ); ?>"
       style="font-family:'Space Mono',monospace;font-size:11px;color:#070945;text-transform:uppercase;letter-spacing:.08em;text-decoration:none;">
      Next: <?php echo esc_html( get_the_title( $next ) ); ?> →
    </a>
  </div>
<?php endif; ?>

<?php endwhile; ?>

<?php get_footer(); ?>
