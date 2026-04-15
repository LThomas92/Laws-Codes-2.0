<?php
/**
 * 404 Page
 * File: 404.php
 *
 * WordPress loads this automatically for any request that returns no content.
 * No ACF fields needed — content is hardcoded (change it here or make it
 * ACF-driven by adding a field group to the "404 Options" options page).
 */

get_header();

// Get a few case studies to suggest
$suggested = get_posts( [
    'post_type'      => 'lc_case_study',
    'posts_per_page' => 3,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
] );
?>

<main class="lc-404" id="main">

    <section class="error-hero">
        <div class="error-hero__inner">

            <div class="error-hero__code" aria-hidden="true">404</div>

            <div class="error-hero__content">
                <div class="error-hero__tag">
                    <span class="error-hero__dash"></span>
                    Page not found
                </div>
                <h1 class="error-hero__h1">
                    This page doesn't<br><em>exist.</em>
                </h1>
                <p class="error-hero__sub">
                    The page you're looking for may have moved, been renamed, or never existed.
                    Try one of the links below or head back home.
                </p>

                <div class="error-hero__actions">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="error-btn error-btn--primary">
                        Back to home
                    </a>
                    <a href="<?php echo esc_url( home_url( '/work/' ) ); ?>" class="error-btn error-btn--ghost">
                        View our work
                    </a>
                </div>
            </div>

        </div>
    </section>

    <?php if ( $suggested ) : ?>
    <section class="error-suggestions section">
        <div class="section__header">
            <div class="section__kicker">While you're here</div>
            <h2 class="section__h">Some of our work.</h2>
        </div>
        <div class="cs-grid error-suggestions__grid">
            <?php foreach ( $suggested as $p ) :
                $bg       = get_field( 'cs_bg_color', $p->ID ) ?: '#0e0c2e';
                $initials = get_field( 'cs_initials',  $p->ID ) ?: '';
                $industry = get_field( 'cs_industry',  $p->ID ) ?: '';
                $tags_raw = get_field( 'cs_tags',      $p->ID ) ?: '';
                $tags     = array_filter( array_map( 'trim', explode( ',', $tags_raw ) ) );
                $kpi_n    = get_field( 'cs_kpi_number', $p->ID ) ?: '';
                $kpi_l    = get_field( 'cs_kpi_label',  $p->ID ) ?: '';
                $link     = get_permalink( $p->ID );
            ?>
            <article class="cs-item">
                <a class="cs-item__link" href="<?php echo esc_url( $link ); ?>"
                   aria-label="View <?php echo esc_attr( $p->post_title ); ?> case study">
                    <div class="cs-thumb" style="background:<?php echo esc_attr( $bg ); ?>" aria-hidden="true">
                        <?php echo esc_html( $initials ); ?>
                    </div>
                    <div class="cs-body">
                        <div class="cs-tags" aria-hidden="true">
                            <?php foreach ( $tags as $t ) : ?>
                                <span class="cs-tag"><?php echo esc_html( $t ); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="cs-title"><?php echo esc_html( $p->post_title ); ?></div>
                        <div class="cs-desc"><?php echo esc_html( get_field( 'cs_description', $p->ID ) ?: $industry ); ?></div>
                    </div>
                    <div class="cs-meta" aria-hidden="true">
                        <?php if ( $kpi_n ) : ?>
                        <div>
                            <div class="cs-kpi-n"><?php echo esc_html( $kpi_n ); ?></div>
                            <div class="cs-kpi-l"><?php echo esc_html( $kpi_l ); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="cs-arr">&#8594;</div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Quick links -->
    <section class="error-links section section--alt">
        <div class="section__header">
            <div class="section__kicker">Explore</div>
            <h2 class="section__h">Where would you like to go?</h2>
        </div>
        <div class="error-nav-grid">
            <?php
            $nav_links = [
                [
                    'label' => 'Work',
                    'desc'  => 'Browse our case studies',
                    'url'   => home_url( '/work/' ),
                    'num'   => '01',
                ],
                [
                    'label' => 'Services',
                    'desc'  => 'See what we offer',
                    'url'   => home_url( '/services/' ),
                    'num'   => '02',
                ],
                [
                    'label' => 'Process',
                    'desc'  => 'How every project runs',
                    'url'   => home_url( '/process/' ),
                    'num'   => '03',
                ],
                [
                    'label' => 'About',
                    'desc'  => 'The studio and the person behind it',
                    'url'   => home_url( '/about/' ),
                    'num'   => '04',
                ],
                [
                    'label' => 'Contact',
                    'desc'  => 'Start a conversation',
                    'url'   => home_url( '/contact/' ),
                    'num'   => '05',
                ],
            ];
            foreach ( $nav_links as $nl ) : ?>
            <a class="error-nav-item" href="<?php echo esc_url( $nl['url'] ); ?>">
                <span class="error-nav-item__num" aria-hidden="true"><?php echo esc_html( $nl['num'] ); ?></span>
                <span class="error-nav-item__label"><?php echo esc_html( $nl['label'] ); ?></span>
                <span class="error-nav-item__desc"><?php echo esc_html( $nl['desc'] ); ?></span>
                <span class="error-nav-item__arrow" aria-hidden="true">&#8594;</span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- CTA -->
    <section class="lc-cta-section">
        <div class="lc-cta-section__kicker">Ready to begin</div>
        <h2 class="lc-cta-section__h">Let's build something<br>you're proud of.</h2>
        <p class="lc-cta-section__sub">No jargon. No hard sell. Just honest work.</p>
        <div class="lc-cta-section__slots">&#9670; Only 2 project slots open this quarter &#9670;</div>
        <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="lc-cta-section__btn">
            Book a discovery call
        </a>
    </section>

</main>

<?php get_footer(); ?>