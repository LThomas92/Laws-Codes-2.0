<?php
/**
 * Global Search — AJAX Handler
 * inc/search-ajax.php
 *
 * Fixes:
 *  1. Case studies are searched via meta_query across ACF fields
 *     (WP native 's' param doesn't index ACF/post-meta content)
 *  2. Results grouped by type: Projects → Pages → Services
 *  3. Empty state only shows when total results === 0
 */

add_action( 'wp_ajax_lc_global_search',        'lc_global_search_handler' );
add_action( 'wp_ajax_nopriv_lc_global_search', 'lc_global_search_handler' );

function lc_global_search_handler() {

    if ( ! check_ajax_referer( 'lc_search_nonce', 'nonce', false ) ) {
        wp_send_json_error( 'Invalid request.', 403 );
    }

    $query = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );

    if ( strlen( $query ) < 2 ) {
        wp_send_json_success( [ 'results' => [], 'total' => 0 ] );
    }

    $results = [];

    // ── 1. Case Studies ───────────────────────────────────────────────────────
    // Native 's' param only searches post_title + post_content.
    // ACF fields live in post_meta so we need a meta_query OR title match.
    // Strategy: run two queries and merge unique IDs.

    $cs_ids = [];

    // 1a. Title match (fast, uses index)
    $title_query = new WP_Query( [
        'post_type'      => 'lc_case_study',
        'posts_per_page' => 10,
        'post_status'    => 'publish',
        's'              => $query,
        'fields'         => 'ids',
    ] );
    $cs_ids = array_merge( $cs_ids, $title_query->posts );

    // 1b. ACF meta fields — search key content fields
    $meta_fields = [
        'cs_description',
        'cs_industry',
        'cs_tags',
        'cs_location',
        'cs_client',
        'cs_tech_stack',
        'cs_services_list',
        'cs_challenge',
        'cs_what_we_built',
        'cs_results',
    ];

    $meta_subqueries = [ 'relation' => 'OR' ];
    foreach ( $meta_fields as $field ) {
        $meta_subqueries[] = [
            'key'     => $field,
            'value'   => $query,
            'compare' => 'LIKE',
        ];
    }

    $meta_query = new WP_Query( [
        'post_type'      => 'lc_case_study',
        'posts_per_page' => 10,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'meta_query'     => $meta_subqueries,
    ] );
    $cs_ids = array_unique( array_merge( $cs_ids, $meta_query->posts ) );

    if ( ! empty( $cs_ids ) ) {
        $group = [ 'type' => 'group', 'label' => 'Projects', 'items' => [] ];

        foreach ( array_slice( $cs_ids, 0, 5 ) as $id ) {
            $group['items'][] = [
                'type'     => 'project',
                'id'       => $id,
                'title'    => get_the_title( $id ),
                'meta'     => implode( ' · ', array_filter( [
                    get_field( 'cs_industry',  $id ),
                    get_field( 'cs_location',  $id ),
                ] ) ),
                'url'      => get_permalink( $id ),
                'bg'       => get_field( 'cs_bg_color', $id ) ?: '#0e0c2e',
                'initials' => get_field( 'cs_initials',  $id ) ?: '',
                'tags'     => get_field( 'cs_tags',      $id ) ?: '',
            ];
        }

        if ( ! empty( $group['items'] ) ) {
            $results[] = $group;
        }
    }

    // ── 2. Pages ──────────────────────────────────────────────────────────────
    $pages_query = new WP_Query( [
        'post_type'      => 'page',
        'posts_per_page' => 4,
        's'              => $query,
        'post_status'    => 'publish',
        'post__not_in'   => array_filter( [
            (int) get_option( 'page_on_front' ),
            (int) get_option( 'page_for_posts' ),
        ] ),
    ] );

    if ( $pages_query->have_posts() ) {
        $group = [ 'type' => 'group', 'label' => 'Pages', 'items' => [] ];

        while ( $pages_query->have_posts() ) {
            $pages_query->the_post();
            $group['items'][] = [
                'type'  => 'page',
                'id'    => get_the_ID(),
                'title' => get_the_title(),
                'meta'  => wp_trim_words( get_the_excerpt() ?: get_the_permalink(), 10 ),
                'url'   => get_permalink(),
                'icon'  => 'page',
            ];
        }
        wp_reset_postdata();

        if ( ! empty( $group['items'] ) ) {
            $results[] = $group;
        }
    }

    // ── 3. Blog posts ─────────────────────────────────────────────────────────
    $posts_query = new WP_Query( [
        'post_type'      => 'post',
        'posts_per_page' => 3,
        's'              => $query,
        'post_status'    => 'publish',
    ] );

    if ( $posts_query->have_posts() ) {
        $group = [ 'type' => 'group', 'label' => 'Blog', 'items' => [] ];

        while ( $posts_query->have_posts() ) {
            $posts_query->the_post();
            $cats = wp_list_pluck( get_the_category(), 'name' );
            $group['items'][] = [
                'type'  => 'post',
                'id'    => get_the_ID(),
                'title' => get_the_title(),
                'meta'  => get_the_date( 'M j, Y' ) . ( $cats ? ' · ' . implode( ', ', $cats ) : '' ),
                'url'   => get_permalink(),
                'icon'  => 'post',
            ];
        }
        wp_reset_postdata();

        if ( ! empty( $group['items'] ) ) {
            $results[] = $group;
        }
    }

    // ── 4. Services (ACF repeater on Services page) ───────────────────────────
    $services_page = get_page_by_path( 'services' );
    if ( $services_page ) {
        $services_list   = get_field( 'services_list', $services_page->ID ) ?: [];
        $matched_svcs    = [];

        foreach ( $services_list as $svc ) {
            $title = $svc['service_title']       ?? '';
            $desc  = $svc['service_description'] ?? '';

            if (
                stripos( $title, $query ) !== false ||
                stripos( $desc,  $query ) !== false
            ) {
                $matched_svcs[] = [
                    'type'  => 'service',
                    'title' => $title,
                    'meta'  => wp_trim_words( $desc, 10 ),
                    'url'   => home_url( '/services/#' . sanitize_title( $title ) ),
                    'icon'  => 'service',
                ];
            }
        }

        if ( ! empty( $matched_svcs ) ) {
            $results[] = [
                'type'  => 'group',
                'label' => 'Services',
                'items' => array_slice( $matched_svcs, 0, 3 ),
            ];
        }
    }

    $total = array_sum( array_map( fn( $g ) => count( $g['items'] ), $results ) );

    wp_send_json_success( [
        'results' => $results,
        'total'   => $total,
        'query'   => $query,
    ] );
}