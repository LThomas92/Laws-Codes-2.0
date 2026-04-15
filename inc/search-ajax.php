<?php
/**
 * Global Search — AJAX Handler
 * File: inc/search-ajax.php
 *
 * Include in functions.php:
 *   require_once get_template_directory() . '/inc/search-ajax.php';
 */

// Register AJAX handlers (logged in + logged out users)
add_action('wp_ajax_lc_global_search',        'lc_global_search_handler');
add_action('wp_ajax_nopriv_lc_global_search', 'lc_global_search_handler');

function lc_global_search_handler() {

    // Verify nonce
    if ( ! check_ajax_referer('lc_search_nonce', 'nonce', false) ) {
        wp_send_json_error('Invalid request.', 403);
    }

    $query = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );

    if ( strlen($query) < 2 ) {
        wp_send_json_success(['results' => [], 'total' => 0]);
    }

    $results = [];

    // ── 1. Case Studies ───────────────────────────────────────────────────────
    $cs_query = new WP_Query([
        'post_type'      => 'lc_case_study',
        'posts_per_page' => 4,
        's'              => $query,
        'post_status'    => 'publish',
        'orderby'        => 'relevance',
    ]);

    if ( $cs_query->have_posts() ) {
        $group = ['type' => 'group', 'label' => 'Projects', 'items' => []];
        while ( $cs_query->have_posts() ) {
            $cs_query->the_post();
            $id = get_the_ID();
            $group['items'][] = [
                'type'     => 'project',
                'id'       => $id,
                'title'    => get_the_title(),
                'meta'     => get_field('cs_industry', $id) . ( get_field('cs_location', $id) ? ' · ' . get_field('cs_location', $id) : '' ),
                'url'      => get_permalink(),
                'bg'       => get_field('cs_bg_color', $id) ?: '#0e0c2e',
                'initials' => get_field('cs_initials',  $id) ?: '',
                'tags'     => get_field('cs_tags', $id) ?: '',
            ];
        }
        wp_reset_postdata();
        if ( ! empty($group['items']) ) $results[] = $group;
    }

    // ── 2. Pages ──────────────────────────────────────────────────────────────
    $pages_query = new WP_Query([
        'post_type'      => 'page',
        'posts_per_page' => 4,
        's'              => $query,
        'post_status'    => 'publish',
        'orderby'        => 'relevance',
        // Exclude utility pages
        'post__not_in'   => array_filter([
            get_option('page_on_front'),
            get_option('page_for_posts'),
        ]),
    ]);

    if ( $pages_query->have_posts() ) {
        $group = ['type' => 'group', 'label' => 'Pages', 'items' => []];
        while ( $pages_query->have_posts() ) {
            $pages_query->the_post();
            $excerpt = get_the_excerpt();
            $group['items'][] = [
                'type'    => 'page',
                'id'      => get_the_ID(),
                'title'   => get_the_title(),
                'meta'    => $excerpt ? wp_trim_words($excerpt, 10) : get_the_permalink(),
                'url'     => get_permalink(),
                'icon'    => 'page',
            ];
        }
        wp_reset_postdata();
        if ( ! empty($group['items']) ) $results[] = $group;
    }

    // ── 3. Blog posts (if used) ───────────────────────────────────────────────
    $posts_query = new WP_Query([
        'post_type'      => 'post',
        'posts_per_page' => 3,
        's'              => $query,
        'post_status'    => 'publish',
        'orderby'        => 'relevance',
    ]);

    if ( $posts_query->have_posts() ) {
        $group = ['type' => 'group', 'label' => 'Blog', 'items' => []];
        while ( $posts_query->have_posts() ) {
            $posts_query->the_post();
            $group['items'][] = [
                'type'  => 'post',
                'id'    => get_the_ID(),
                'title' => get_the_title(),
                'meta'  => get_the_date('M j, Y') . ' · ' . implode(', ', wp_list_pluck(get_the_category(), 'name')),
                'url'   => get_permalink(),
                'icon'  => 'post',
            ];
        }
        wp_reset_postdata();
        if ( ! empty($group['items']) ) $results[] = $group;
    }

    // ── 4. Services keyword matching (ACF repeater content) ──────────────────
    // Manually check services page since ACF repeater content isn't indexed by WP search
    $services_page = get_page_by_path('services');
    if ( $services_page ) {
        $services_list = get_field('services_list', $services_page->ID) ?: [];
        $matched_services = [];
        foreach ($services_list as $svc) {
            $title = $svc['service_title'] ?? '';
            $desc  = $svc['service_description'] ?? '';
            if (
                stripos($title, $query) !== false ||
                stripos($desc,  $query) !== false
            ) {
                $matched_services[] = [
                    'type'  => 'service',
                    'title' => $title,
                    'meta'  => wp_trim_words($desc, 10),
                    'url'   => home_url('/services/') . '#' . sanitize_title($title),
                    'icon'  => 'service',
                ];
            }
        }
        if ( ! empty($matched_services) ) {
            $results[] = ['type' => 'group', 'label' => 'Services', 'items' => array_slice($matched_services, 0, 3)];
        }
    }

    $total = array_sum(array_map(fn($g) => count($g['items']), $results));

    wp_send_json_success([
        'results' => $results,
        'total'   => $total,
        'query'   => $query,
    ]);
}