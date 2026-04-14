<?php
/**
 * Laws & Codes — archive-lc_project.php
 * All projects / portfolio archive
 */
defined( 'ABSPATH' ) || exit;
get_header();

$categories = get_terms([
  'taxonomy'   => 'lc_project_cat',
  'hide_empty' => true,
]);
?>

<div style="padding:64px 48px 0;background:#fff;border-bottom:1px solid rgba(7,9,69,0.11)">
  <div style="font-family:'Space Mono',monospace;font-size:10px;color:#c9a882;letter-spacing:.14em;text-transform:uppercase;margin-bottom:10px;font-weight:700">
    Our work
  </div>
  <h1 style="font-family:'Playfair Display',Georgia,serif;font-size:52px;font-weight:400;color:#070945;line-height:1.08;margin-bottom:14px;letter-spacing:-.01em">
    Case studies
  </h1>
  <p style="font-size:14px;color:#6b6b7e;line-height:1.8;font-weight:300;max-width:480px;margin-bottom:40px">
    14 digital legacies built across beauty, hospitality, real estate, media, and more.
  </p>

  <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:0">
      <a href="<?php echo esc_url( get_post_type_archive_link( 'lc_project' ) ); ?>"
         style="font-family:'Space Mono',monospace;font-size:10px;padding:6px 14px;border-radius:3px;text-decoration:none;letter-spacing:.05em;background:<?php echo is_post_type_archive() && ! is_tax() ? '#070945' : '#fff'; ?>;color:<?php echo is_post_type_archive() && ! is_tax() ? '#E8CCB2' : '#6b6b7e'; ?>;border:1px solid rgba(7,9,69,0.11)">
        All
      </a>
      <?php foreach ( $categories as $cat ) : ?>
        <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
           style="font-family:'Space Mono',monospace;font-size:10px;padding:6px 14px;border-radius:3px;text-decoration:none;letter-spacing:.05em;background:<?php echo is_tax( 'lc_project_cat', $cat ) ? '#070945' : '#fff'; ?>;color:<?php echo is_tax( 'lc_project_cat', $cat ) ? '#E8CCB2' : '#6b6b7e'; ?>;border:1px solid rgba(7,9,69,0.11)">
          <?php echo esc_html( $cat->name ); ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- PROJECT LIST -->
<div class="cs-list" style="border-radius:0;border-left:none;border-right:none;box-shadow:none">
  <?php if ( have_posts() ) :
    while ( have_posts() ) :
      the_post();
      $id     = get_the_ID();
      $cat    = wp_get_post_terms( $id, 'lc_project_cat' );
      $bg     = get_post_meta( $id, '_lc_bg_color',      true ) ?: '#0e0c2e';
      $kpi_n  = get_post_meta( $id, '_lc_kpi_1_num',     true );
      $kpi_l  = get_post_meta( $id, '_lc_kpi_1_label',   true );
      $teaser = get_post_meta( $id, '_lc_result_teaser', true );
      $init   = strtoupper( substr( get_the_title(), 0, 2 ) );
  ?>
    <a href="<?php the_permalink(); ?>" class="cs-item">
      <div class="cs-thumb" style="background:<?php echo esc_attr( $bg ); ?>">
        <?php if ( has_post_thumbnail() ) : ?>
          <?php the_post_thumbnail( 'lc-project-card', [ 'style' => 'width:100%;height:100%;object-fit:cover;', 'alt' => get_the_title() ] ); ?>
        <?php else : ?>
          <?php echo esc_html( $init ); ?>
        <?php endif; ?>
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
  <?php
    endwhile;
    the_posts_pagination([
      'prev_text' => '← Previous',
      'next_text' => 'Next →',
      'before_page_number' => '<span>',
      'after_page_number'  => '</span>',
    ]);
  else : ?>
    <p style="padding:48px;text-align:center;color:#6b6b7e;font-size:14px">
      No projects found. Add some from the WordPress admin under <strong>Projects</strong>.
    </p>
  <?php endif; ?>
</div>

<!-- CTA -->
<section class="cta-section">
  <div class="cta-kicker">Ready to begin</div>
  <h2 class="cta-heading">Want a site like these?</h2>
  <p class="cta-sub">Let's talk about your project — no pressure, no jargon.</p>
  <p class="cta-slots">◆ Only 2 project slots open this quarter ◆</p>
  <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="cta-button">
    Book a discovery call
  </a>
</section>

<?php get_footer(); ?>
