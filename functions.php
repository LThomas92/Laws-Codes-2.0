<?php
/**
 * Laws & Codes — functions.php
 * Theme setup, enqueues, CPTs, REST API endpoints
 */

defined( 'ABSPATH' ) || exit;

// ── CONSTANTS ─────────────────────────────────────────────
define( 'LC_VERSION', '1.0.0' );
define( 'LC_DIR',     get_template_directory() );
define( 'LC_URI',     get_template_directory_uri() );

// ── THEME SETUP ───────────────────────────────────────────
function lc_setup() {
    load_theme_textdomain( 'laws-codes', LC_DIR . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [
        'search-form', 'comment-form', 'comment-list',
        'gallery', 'caption', 'style', 'script',
    ]);
    add_theme_support( 'custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    // Image sizes
    add_image_size( 'lc-project-thumb',  600,  400, true );
    add_image_size( 'lc-project-hero',  1200,  700, true );
    add_image_size( 'lc-project-card',   400,  280, true );

    // Nav menus
    register_nav_menus([
        'primary' => __( 'Primary Menu',    'laws-codes' ),
        'footer'  => __( 'Footer Menu',     'laws-codes' ),
        'social'  => __( 'Social Menu',     'laws-codes' ),
    ]);
}
add_action( 'after_setup_theme', 'lc_setup' );

// ── ASSET MANIFEST HELPER ─────────────────────────────────
/**
 * Reads dist/assets.json (written by assets-webpack-plugin) to get
 * content-hashed filenames in production. Falls back to plain names
 * when the manifest is absent (first install / dev mode).
 */
function lc_asset( string $key, string $type ): string {
    static $manifest = null;

    if ( $manifest === null ) {
        $path = LC_DIR . '/dist/assets.json';
        $manifest = file_exists( $path )
            ? (array) json_decode( file_get_contents( $path ), true )
            : [];
    }

    // assets.json stores paths like "js/main-abc123.js"
    $file = $manifest[ $key ][ $type ] ?? null;
    if ( $file ) {
        return LC_URI . '/dist/' . ltrim( $file, '/' );
    }

    // Sensible fallback matching webpack output names
    $ext = $type === 'js' ? 'js/main.js' : 'css/style.css';
    return LC_URI . '/dist/' . $ext;
}

// ── SCRIPTS & STYLES ──────────────────────────────────────
function lc_enqueue() {
    // Google Fonts
    wp_enqueue_style(
        'lc-google-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,700;1,400;1,700&family=Inter:wght@300;400;500&family=Space+Mono:wght@400;700&display=swap',
        [],
        null
    );

    // Compiled CSS — webpack outputs dist/css/style.css (MiniCssExtractPlugin)
    wp_enqueue_style(
        'lc-main',
        lc_asset( 'main', 'css' ),
        [ 'lc-google-fonts' ],
        LC_VERSION
    );

    // Main JS bundle — src/index.js imports all modules
    wp_enqueue_script(
        'lc-main',
        lc_asset( 'main', 'js' ),
        [],
        LC_VERSION,
        true   // load in footer
    );

    // Localize shared data (must come after enqueue)
    wp_localize_script( 'lc-main', 'LC_Data', [
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'lc_nonce' ),
        'siteUrl'   => get_site_url(),
        'restUrl'   => rest_url( 'lawscodes/v1/' ),
    ]);

    // Pass featured project data to the switcher (front page only)
    if ( is_front_page() ) {
        wp_localize_script( 'lc-main', 'LC_Projects', lc_get_featured_projects() );
    }
}
add_action( 'wp_enqueue_scripts', 'lc_enqueue' );

// ── GRAVITY FORMS INTEGRATION ─────────────────────────────
/**
 * Move Gravity Forms scripts to the footer to avoid render-blocking.
 * Add the filter only when GF is active.
 */
if ( class_exists( 'GFForms' ) ) {
    add_filter( 'gform_init_scripts_footer', '__return_true' );

    /**
     * Apply theme CSS classes to GF form fields so they inherit
     * the theme's form-input / form-label / form-submit styles.
     */
    add_filter( 'gform_field_css_class', function( $classes, $field, $form ) {
        $classes .= ' lc-gf-field';
        return $classes;
    }, 10, 3 );

    /**
     * Wrap the GF submit button in the theme's button class.
     */
    add_filter( 'gform_submit_button', function( $button, $form ) {
        return str_replace( 'class=\'gform_button', 'class=\'form-submit gform_button', $button );
    }, 10, 2 );
}

// ── ACF — LOCAL JSON SYNC ──────────────────────────────────
/**
 * Tell ACF to save / load field groups from the theme's acf-json folder.
 * This keeps field group definitions in version control.
 */
if ( function_exists( 'acf_add_local_field_group' ) || defined( 'ACF_VERSION' ) ) {
    add_filter( 'acf/settings/save_json', function() {
        return LC_DIR . '/acf-json';
    });

    add_filter( 'acf/settings/load_json', function( $paths ) {
        $paths[] = LC_DIR . '/acf-json';
        return $paths;
    });
}

// ── ACF → PROJECT META BRIDGE ──────────────────────────────
/**
 * When ACF is active, lc_get_featured_projects() reads ACF fields
 * via get_field() for a cleaner API. When ACF is absent it falls back
 * to raw get_post_meta() (existing behaviour — no change needed there).
 *
 * ACF field names mirror the _lc_* meta keys defined below but without
 * the leading underscore, e.g. "lc_client_name", "lc_bg_color", etc.
 * Set these up in ACF > Field Groups > Project Details.
 */
function lc_project_field( int $post_id, string $key ): string {
    $acf_key = ltrim( $key, '_' ); // "_lc_bg_color" → "lc_bg_color"
    if ( function_exists( 'get_field' ) ) {
        return (string) get_field( $acf_key, $post_id );
    }
    return (string) get_post_meta( $post_id, $key, true );
}

// ── CUSTOM POST TYPE: PROJECTS ─────────────────────────────
function lc_register_project_cpt() {
    register_post_type( 'lc_project', [
        'labels'      => [
            'name'               => __( 'Projects',           'laws-codes' ),
            'singular_name'      => __( 'Project',            'laws-codes' ),
            'add_new_item'       => __( 'Add New Project',    'laws-codes' ),
            'edit_item'          => __( 'Edit Project',       'laws-codes' ),
            'new_item'           => __( 'New Project',        'laws-codes' ),
            'view_item'          => __( 'View Project',       'laws-codes' ),
            'search_items'       => __( 'Search Projects',    'laws-codes' ),
            'not_found'          => __( 'No projects found.', 'laws-codes' ),
            'menu_name'          => __( 'Projects',           'laws-codes' ),
        ],
        'public'            => true,
        'show_in_rest'      => true,
        'has_archive'       => true,
        'menu_icon'         => 'dashicons-portfolio',
        'menu_position'     => 5,
        'supports'          => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'rewrite'           => [ 'slug' => 'work' ],
        'show_in_graphql'   => false,
    ]);
}
add_action( 'init', 'lc_register_project_cpt' );

// ── CUSTOM TAXONOMY: PROJECT CATEGORY ─────────────────────
function lc_register_project_tax() {
    register_taxonomy( 'lc_project_cat', 'lc_project', [
        'labels'        => [
            'name'          => __( 'Project Categories', 'laws-codes' ),
            'singular_name' => __( 'Project Category',  'laws-codes' ),
        ],
        'public'        => true,
        'hierarchical'  => true,
        'show_in_rest'  => true,
        'rewrite'       => [ 'slug' => 'work-category' ],
    ]);
}
add_action( 'init', 'lc_register_project_tax' );

// ── CUSTOM FIELDS FOR PROJECTS (via post meta) ─────────────
/**
 * Meta fields used per project:
 *  _lc_client_name     — Client business name
 *  _lc_industry        — e.g. Beauty & Wellness
 *  _lc_location        — e.g. New York, NY
 *  _lc_year            — e.g. 2024
 *  _lc_timeline        — e.g. 6 weeks
 *  _lc_services        — comma-separated: Design, Dev, Brand
 *  _lc_tech_stack      — comma-separated: WordPress, WooCommerce...
 *  _lc_kpi_1_num       — e.g. 3×
 *  _lc_kpi_1_label     — e.g. Booking increase
 *  _lc_kpi_2_num       — e.g. 6wk
 *  _lc_kpi_2_label     — e.g. Delivery
 *  _lc_kpi_3_num       — e.g. 12
 *  _lc_kpi_3_label     — e.g. Products launched
 *  _lc_kpi_4_num       — e.g. 100%
 *  _lc_kpi_4_label     — e.g. Mobile optimized
 *  _lc_github_url      — GitHub repo URL
 *  _lc_live_url        — Live site URL
 *  _lc_featured        — 1 if shown in homepage switcher
 *  _lc_switcher_order  — int, order in switcher
 *  _lc_bg_color        — hex for project card bg, e.g. #0e0c2e
 *  _lc_category_label  — e.g. Beauty · E-Commerce
 *  _lc_result_teaser   — one-liner for case study list
 *  _lc_challenge       — challenge paragraph (HTML)
 *  _lc_solution        — solution paragraph (HTML)
 *  _lc_results         — results paragraph (HTML)
 */
function lc_register_project_meta() {
    $text_fields = [
        '_lc_client_name', '_lc_industry', '_lc_location', '_lc_year',
        '_lc_timeline', '_lc_services', '_lc_tech_stack',
        '_lc_kpi_1_num', '_lc_kpi_1_label', '_lc_kpi_2_num', '_lc_kpi_2_label',
        '_lc_kpi_3_num', '_lc_kpi_3_label', '_lc_kpi_4_num', '_lc_kpi_4_label',
        '_lc_github_url', '_lc_live_url', '_lc_bg_color', '_lc_category_label',
        '_lc_result_teaser', '_lc_challenge', '_lc_solution', '_lc_results',
    ];

    foreach ( $text_fields as $key ) {
        register_post_meta( 'lc_project', $key, [
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => 'string',
            'auth_callback' => fn() => current_user_can( 'edit_posts' ),
        ]);
    }

    register_post_meta( 'lc_project', '_lc_featured', [
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'boolean',
        'auth_callback' => fn() => current_user_can( 'edit_posts' ),
    ]);

    register_post_meta( 'lc_project', '_lc_switcher_order', [
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'integer',
        'auth_callback' => fn() => current_user_can( 'edit_posts' ),
    ]);
}
add_action( 'init', 'lc_register_project_meta' );

// ── FEATURED PROJECTS FOR JS SWITCHER ─────────────────────
function lc_get_featured_projects(): array {
    $query = new WP_Query([
        'post_type'      => 'lc_project',
        'posts_per_page' => 6,
        'meta_query'     => [[
            'key'   => '_lc_featured',
            'value' => '1',
        ]],
        'meta_key'  => '_lc_switcher_order',
        'orderby'   => 'meta_value_num',
        'order'     => 'ASC',
    ]);

    $projects = [];

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();

            $projects[] = [
                'id'             => $id,
                'title'          => get_the_title(),
                'slug'           => get_post_field( 'post_name', $id ),
                'url'            => get_permalink( $id ),
                'industry'       => lc_project_field( $id, '_lc_industry' ),
                'categoryLabel'  => lc_project_field( $id, '_lc_category_label' ),
                'bgColor'        => lc_project_field( $id, '_lc_bg_color' ) ?: '#0e0c2e',
                'kpi1Num'        => lc_project_field( $id, '_lc_kpi_1_num' ),
                'kpi1Label'      => lc_project_field( $id, '_lc_kpi_1_label' ),
                'kpi2Num'        => lc_project_field( $id, '_lc_kpi_2_num' ),
                'kpi2Label'      => lc_project_field( $id, '_lc_kpi_2_label' ),
                'techStack'      => array_map(
                    'trim',
                    explode( ',', lc_project_field( $id, '_lc_tech_stack' ) )
                ),
                'thumbnail'      => get_the_post_thumbnail_url( $id, 'lc-project-card' ),
            ];
        }
        wp_reset_postdata();
    }

    return $projects;
}

// ── CUSTOM TESTIMONIALS POST TYPE ─────────────────────────
function lc_register_testimonial_cpt() {
    register_post_type( 'lc_testimonial', [
        'labels'        => [
            'name'          => __( 'Testimonials',        'laws-codes' ),
            'singular_name' => __( 'Testimonial',         'laws-codes' ),
            'add_new_item'  => __( 'Add New Testimonial', 'laws-codes' ),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-format-quote',
        'menu_position' => 6,
        'supports'      => [ 'title', 'editor', 'custom-fields' ],
    ]);

    // Meta: client name, role, company, star rating, project link
    $testi_meta = [
        '_lc_testi_client'  => 'string',
        '_lc_testi_role'    => 'string',
        '_lc_testi_company' => 'string',
        '_lc_testi_initials'=> 'string',
        '_lc_testi_project' => 'string',
    ];

    foreach ( $testi_meta as $key => $type ) {
        register_post_meta( 'lc_testimonial', $key, [
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => $type,
            'auth_callback' => fn() => current_user_can( 'edit_posts' ),
        ]);
    }

    register_post_meta( 'lc_testimonial', '_lc_testi_stars', [
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'integer',
        'auth_callback' => fn() => current_user_can( 'edit_posts' ),
    ]);
}
add_action( 'init', 'lc_register_testimonial_cpt' );

// ── CONTACT FORM AJAX HANDLER ──────────────────────────────
function lc_handle_contact_form() {
    check_ajax_referer( 'lc_nonce', 'nonce' );

    $fields = [
        'first_name' => sanitize_text_field( $_POST['first_name'] ?? '' ),
        'last_name'  => sanitize_text_field( $_POST['last_name']  ?? '' ),
        'email'      => sanitize_email(      $_POST['email']      ?? '' ),
        'business'   => sanitize_text_field( $_POST['business']   ?? '' ),
        'service'    => sanitize_text_field( $_POST['service']    ?? '' ),
        'message'    => sanitize_textarea_field( $_POST['message'] ?? '' ),
    ];

    if ( empty( $fields['email'] ) || ! is_email( $fields['email'] ) ) {
        wp_send_json_error( [ 'message' => 'Please provide a valid email address.' ] );
    }

    if ( empty( $fields['first_name'] ) || empty( $fields['message'] ) ) {
        wp_send_json_error( [ 'message' => 'Please fill in all required fields.' ] );
    }

    $to      = get_option( 'admin_email' );
    $subject = sprintf( 'New project enquiry from %s %s', $fields['first_name'], $fields['last_name'] );
    $body    = lc_build_contact_email( $fields );
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        sprintf( 'Reply-To: %s <%s>', $fields['first_name'] . ' ' . $fields['last_name'], $fields['email'] ),
    ];

    $sent = wp_mail( $to, $subject, $body, $headers );

    if ( $sent ) {
        wp_send_json_success( [ 'message' => 'Message sent. We\'ll be in touch within one business day.' ] );
    } else {
        wp_send_json_error( [ 'message' => 'Something went wrong — please email hello@lawscodes.com directly.' ] );
    }
}
add_action( 'wp_ajax_nopriv_lc_contact', 'lc_handle_contact_form' );
add_action( 'wp_ajax_lc_contact',        'lc_handle_contact_form' );

function lc_build_contact_email( array $f ): string {
    return sprintf(
        '<h2 style="font-family:serif;color:#070945;">New enquiry — Laws &amp; Codes</h2>
        <table style="border-collapse:collapse;width:100%%;font-family:sans-serif;font-size:14px;">
            <tr><td style="padding:8px 12px;border:1px solid #ddd;color:#666;">Name</td><td style="padding:8px 12px;border:1px solid #ddd;">%s %s</td></tr>
            <tr><td style="padding:8px 12px;border:1px solid #ddd;color:#666;">Email</td><td style="padding:8px 12px;border:1px solid #ddd;"><a href="mailto:%s">%s</a></td></tr>
            <tr><td style="padding:8px 12px;border:1px solid #ddd;color:#666;">Business</td><td style="padding:8px 12px;border:1px solid #ddd;">%s</td></tr>
            <tr><td style="padding:8px 12px;border:1px solid #ddd;color:#666;">Service</td><td style="padding:8px 12px;border:1px solid #ddd;">%s</td></tr>
            <tr><td style="padding:8px 12px;border:1px solid #ddd;color:#666;vertical-align:top;">Message</td><td style="padding:8px 12px;border:1px solid #ddd;">%s</td></tr>
        </table>',
        esc_html( $f['first_name'] ), esc_html( $f['last_name'] ),
        esc_attr( $f['email'] ), esc_html( $f['email'] ),
        esc_html( $f['business'] ),
        esc_html( $f['service'] ),
        nl2br( esc_html( $f['message'] ) )
    );
}

// ── QUIZ SUBMISSION AJAX ───────────────────────────────────
function lc_handle_quiz_lead() {
    check_ajax_referer( 'lc_nonce', 'nonce' );

    $result  = sanitize_text_field( $_POST['result']  ?? '' );
    $answers = array_map( 'sanitize_text_field', (array) ( $_POST['answers'] ?? [] ) );

    // Store as a simple post for lead tracking
    $post_id = wp_insert_post([
        'post_type'   => 'lc_quiz_lead',
        'post_title'  => sprintf( 'Quiz lead — %s — %s', $result, current_time( 'mysql' ) ),
        'post_status' => 'private',
        'meta_input'  => [
            '_lc_quiz_result'  => $result,
            '_lc_quiz_answers' => implode( ', ', $answers ),
            '_lc_quiz_date'    => current_time( 'mysql' ),
        ],
    ]);

    wp_send_json_success([ 'redirect' => get_page_link( get_page_by_path( 'contact' ) ) ]);
}
add_action( 'wp_ajax_nopriv_lc_quiz_lead', 'lc_handle_quiz_lead' );
add_action( 'wp_ajax_lc_quiz_lead',        'lc_handle_quiz_lead' );

// ── WIDGET AREAS ──────────────────────────────────────────
function lc_widgets_init() {
    register_sidebar([
        'name'          => __( 'Footer Widget Area', 'laws-codes' ),
        'id'            => 'footer-1',
        'description'   => __( 'Widgets in the footer.', 'laws-codes' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
}
add_action( 'widgets_init', 'lc_widgets_init' );

// ── CLEAN UP WP HEAD ──────────────────────────────────────
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

// ── CUSTOM IMAGE SIZES IN MEDIA LIBRARY ───────────────────
function lc_custom_image_sizes( $sizes ) {
    return array_merge( $sizes, [
        'lc-project-thumb' => __( 'Project Thumbnail', 'laws-codes' ),
        'lc-project-hero'  => __( 'Project Hero',      'laws-codes' ),
        'lc-project-card'  => __( 'Project Card',      'laws-codes' ),
    ]);
}
add_filter( 'image_size_names_choose', 'lc_custom_image_sizes' );

// ── SEO TITLE HELPER ──────────────────────────────────────
function lc_document_title_separator( $sep ) {
    return '—';
}
add_filter( 'document_title_separator', 'lc_document_title_separator' );

// ── EXCERPT LENGTH ────────────────────────────────────────
add_filter( 'excerpt_length', fn() => 28 );
add_filter( 'excerpt_more',   fn() => '...' );

//SAVE LOCAL JSON

add_filter( 'acf/settings/save_json', function () {
    return get_stylesheet_directory() . '/acf-json';
} );

add_filter( 'acf/settings/load_json', function ( $paths ) {
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
} );


// ══════════════════════════════════════════════════════════════════════════════
// 4. STRIPE — WP_AJAX handlers registration
//    (The actual functions live in stripe/page-payment.php which is loaded
//     when the template is rendered. To use them globally, you can move the
//     function definitions here and remove them from the template.)
// ══════════════════════════════════════════════════════════════════════════════

// Uncomment if you move lc_create_payment_intent() here:
// add_action( 'wp_ajax_nopriv_lc_create_payment_intent', 'lc_create_payment_intent' );
// add_action( 'wp_ajax_lc_create_payment_intent',        'lc_create_payment_intent' );


// ══════════════════════════════════════════════════════════════════════════════
// 5. STRIPE WEBHOOK ENDPOINT
//    Add this early (before headers send) so the webhook fires on any request.
//    Alternatively handle via a custom REST route (see below).
// ══════════════════════════════════════════════════════════════════════════════
add_action( 'init', function () {
    if ( isset( $_GET['stripe_webhook'] ) && $_GET['stripe_webhook'] === '1' ) {
        if ( function_exists( 'lc_handle_stripe_webhook' ) ) {
            lc_handle_stripe_webhook();
            exit;
        }
    }
} );


// ══════════════════════════════════════════════════════════════════════════════
// 6. ALTERNATIVE: Stripe webhook as WP REST API endpoint
//    URL: https://yourdomain.com/wp-json/lawscodes/v1/stripe-webhook
//    (Use this URL in Stripe Dashboard instead of ?stripe_webhook=1)
// ══════════════════════════════════════════════════════════════════════════════
add_action( 'rest_api_init', function () {

    register_rest_route( 'lawscodes/v1', '/stripe-webhook', [
        'methods'             => 'POST',
        'callback'            => 'lc_rest_stripe_webhook',
        'permission_callback' => '__return_true',
    ] );

} );

function lc_rest_stripe_webhook( WP_REST_Request $request ) {
    if ( ! defined( 'STRIPE_SECRET_KEY' ) ) {
        return new WP_REST_Response( [ 'error' => 'Not configured' ], 400 );
    }

    $theme_dir = get_template_directory();
    if ( file_exists( $theme_dir . '/vendor/autoload.php' ) ) {
        require_once $theme_dir . '/vendor/autoload.php';
    }

    \Stripe\Stripe::setApiKey( STRIPE_SECRET_KEY );

    $payload = $request->get_body();
    $sig     = $request->get_header( 'stripe-signature' );

    try {
        $event = \Stripe\Webhook::constructEvent( $payload, $sig, STRIPE_WEBHOOK_SECRET );
    } catch ( \Exception $e ) {
        return new WP_REST_Response( [ 'error' => $e->getMessage() ], 400 );
    }

    switch ( $event->type ) {
        case 'payment_intent.succeeded':
            $intent = $event->data->object;
            lc_notify_payment_received( $intent );
            break;
        case 'payment_intent.payment_failed':
            // Log or email client about failed payment
            break;
    }

    return new WP_REST_Response( [ 'received' => true ], 200 );
}


// ══════════════════════════════════════════════════════════════════════════════
// 7. HELPER: Generate a payment link programmatically
//    Use this in admin or via a custom dashboard to send invoice links.
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Generate a Stripe payment page URL.
 *
 * @param string $invoice_id  e.g. 'INV-2024-001'
 * @param int    $amount_usd  Amount in dollars (will be converted to cents)
 * @param string $description e.g. '50% Deposit — Project Name'
 * @return string
 */
function lc_payment_url( string $invoice_id, int $amount_usd, string $description = '' ): string {
    $page = get_page_by_path( 'payment' );
    if ( ! $page ) return home_url('/payment/');

    return add_query_arg( [
        'invoice' => rawurlencode( $invoice_id ),
        'amount'  => $amount_usd * 100,
        'desc'    => rawurlencode( $description ?: "Invoice {$invoice_id}" ),
    ], get_permalink( $page ) );
}

// Example usage:
// $link = lc_payment_url( 'INV-2024-042', 1200, '50% Deposit — Brow Beast Redesign' );
// Then email it to the client.


//Project Image
add_image_size( 'lc-project-hero', 800, 380, true ); // true = hard crop

require_once get_template_directory() . '/inc/search-ajax.php';

// Enqueue the JS
add_action('wp_enqueue_scripts', function() {
  wp_enqueue_script(
    'lawscodes-search',
    get_template_directory_uri() . '/src/modules/search.js',
    ['lawscodes-main'], // depends on your main script handle
    null,
    true
  );
});