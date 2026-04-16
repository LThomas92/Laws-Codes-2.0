<?php
/**
 * Social Meta Tags — Open Graph + Twitter Card
 * inc/social-meta.php
 *
 * Include in functions.php:
 *   require_once LC_DIR . '/inc/social-meta.php';
 *
 * ACF fields used (all optional — falls back to defaults):
 *   seo_title       (text)   — overrides page title in OG
 *   seo_description (textarea) — overrides excerpt/tagline
 *   seo_image       (image, return: array) — overrides default share image
 *
 * Default share image: upload a 1200×630 image to your theme at:
 *   /images/social-share.jpg
 */

add_action( 'wp_head', 'lc_social_meta_tags', 1 );

function lc_social_meta_tags() {

    // ── Resolve title ──────────────────────────────────────────────────────────
    $site_name = get_bloginfo( 'name' );

    if ( is_singular() ) {
        $seo_title = get_field( 'seo_title' ) ?: get_the_title();
        $og_title  = $seo_title . ' — ' . $site_name;
    } elseif ( is_front_page() ) {
        $og_title = $site_name . ' — Custom WordPress & E-Commerce Development';
    } else {
        $og_title = wp_title( '—', false, 'right' ) . $site_name;
    }

    // ── Resolve description ────────────────────────────────────────────────────
    $default_desc = 'Custom websites, e-commerce, and digital experiences for businesses that refuse to blend in. Built from scratch — no templates.';

    if ( is_singular() ) {
        $seo_desc = get_field( 'seo_description' )
            ?: ( has_excerpt() ? get_the_excerpt() : $default_desc );
    } else {
        $seo_desc = get_bloginfo( 'description' ) ?: $default_desc;
    }
    $seo_desc = wp_strip_all_tags( $seo_desc );
    // Trim to ~160 chars for Twitter
    if ( strlen( $seo_desc ) > 160 ) {
        $seo_desc = substr( $seo_desc, 0, 157 ) . '...';
    }

    // ── Resolve image ──────────────────────────────────────────────────────────
    $default_img = get_template_directory_uri() . '/images/social-share.jpg';

    if ( is_singular() ) {
        $seo_img_field = get_field( 'seo_image' );
        if ( $seo_img_field ) {
            $og_img = $seo_img_field['url'] ?? $default_img;
        } elseif ( has_post_thumbnail() ) {
            $og_img = get_the_post_thumbnail_url( null, 'full' );
        } else {
            $og_img = $default_img;
        }
    } else {
        $og_img = $default_img;
    }

    // ── Resolve URL ───────────────────────────────────────────────────────────
    $og_url = is_singular() ? get_permalink() : home_url( add_query_arg( [] ) );

    // ── Resolve type ─────────────────────────────────────────────────────────
    $og_type = is_singular( 'post' ) ? 'article' : 'website';

    ?>
<!-- Open Graph / Facebook -->
<meta property="og:type"        content="<?php echo esc_attr( $og_type ); ?>">
<meta property="og:url"         content="<?php echo esc_url( $og_url ); ?>">
<meta property="og:site_name"   content="<?php echo esc_attr( $site_name ); ?>">
<meta property="og:title"       content="<?php echo esc_attr( $og_title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $seo_desc ); ?>">
<meta property="og:image"       content="<?php echo esc_url( $og_img ); ?>">
<meta property="og:image:width"  content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale"      content="en_US">

<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?php echo esc_attr( $og_title ); ?>">
<meta name="twitter:description" content="<?php echo esc_attr( $seo_desc ); ?>">
<meta name="twitter:image"       content="<?php echo esc_url( $og_img ); ?>">

<!-- Canonical -->
<link rel="canonical" href="<?php echo esc_url( $og_url ); ?>">
    <?php
}