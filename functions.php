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
    add_image_size( 'lc-project-hero',   800,  380, true );
    add_image_size( 'lc-project-card',   400,  280, true );

    // Nav menus
    register_nav_menus([
        'primary' => __( 'Primary Menu', 'laws-codes' ),
        'footer'  => __( 'Footer Menu',  'laws-codes' ),
        'social'  => __( 'Social Menu',  'laws-codes' ),
    ]);
}
add_action( 'after_setup_theme', 'lc_setup' );

// ── ASSET MANIFEST HELPER ─────────────────────────────────
function lc_asset( string $key, string $type ): string {
    static $manifest = null;

    if ( $manifest === null ) {
        $path     = LC_DIR . '/dist/assets.json';
        $manifest = file_exists( $path )
            ? (array) json_decode( file_get_contents( $path ), true )
            : [];
    }

    $file = $manifest[ $key ][ $type ] ?? null;
    if ( $file ) {
        return LC_URI . '/dist/' . ltrim( $file, '/' );
    }

    return LC_URI . '/dist/' . ( $type === 'js' ? 'js/main.js' : 'css/style.css' );
}

// ── SCRIPTS & STYLES ──────────────────────────────────────
function lc_enqueue() {
    wp_enqueue_style(
        'lc-google-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,700;1,400;1,700&family=Inter:wght@300;400;500&family=Space+Mono:wght@400;700&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'lc-main',
        lc_asset( 'main', 'css' ),
        [ 'lc-google-fonts' ],
        LC_VERSION
    );

    wp_enqueue_script(
        'lc-main',
        lc_asset( 'main', 'js' ),
        [],
        LC_VERSION,
        true
    );

    wp_localize_script( 'lc-main', 'LC_Data', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'lc_nonce' ),
        'siteUrl' => get_site_url(),
        'restUrl' => rest_url( 'lawscodes/v1/' ),
    ]);

    if ( is_front_page() ) {
        wp_localize_script( 'lc-main', 'LC_Projects', lc_get_featured_projects() );
    }

    wp_enqueue_script(
        'lawscodes-search',
        LC_URI . '/src/modules/search.js',
        [ 'lc-main' ],
        LC_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'lc_enqueue' );

// ── GRAVITY FORMS ─────────────────────────────────────────
if ( class_exists( 'GFForms' ) ) {
    add_filter( 'gform_init_scripts_footer', '__return_true' );

    add_filter( 'gform_field_css_class', function( $classes, $field, $form ) {
        $classes .= ' lc-gf-field';
        return $classes;
    }, 10, 3 );

    add_filter( 'gform_submit_button', function( $button, $form ) {
        return str_replace( 'class=\'gform_button', 'class=\'form-submit gform_button', $button );
    }, 10, 2 );
}

// ── ACF LOCAL JSON SYNC ───────────────────────────────────
add_filter( 'acf/settings/save_json', function () {
    return get_stylesheet_directory() . '/acf-json';
} );

add_filter( 'acf/settings/load_json', function ( $paths ) {
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
} );

// ── ACF → PROJECT META BRIDGE ─────────────────────────────
function lc_project_field( int $post_id, string $key ): string {
    $acf_key = ltrim( $key, '_' );
    if ( function_exists( 'get_field' ) ) {
        return (string) get_field( $acf_key, $post_id );
    }
    return (string) get_post_meta( $post_id, $key, true );
}

// ── CPT: lc_project ───────────────────────────────────────
function lc_register_project_cpt() {
    register_post_type( 'lc_project', [
        'labels' => [
            'name'          => __( 'Projects',           'laws-codes' ),
            'singular_name' => __( 'Project',            'laws-codes' ),
            'add_new_item'  => __( 'Add New Project',    'laws-codes' ),
            'edit_item'     => __( 'Edit Project',       'laws-codes' ),
            'new_item'      => __( 'New Project',        'laws-codes' ),
            'view_item'     => __( 'View Project',       'laws-codes' ),
            'search_items'  => __( 'Search Projects',    'laws-codes' ),
            'not_found'     => __( 'No projects found.', 'laws-codes' ),
            'menu_name'     => __( 'Projects',           'laws-codes' ),
        ],
        'public'        => true,
        'show_in_rest'  => true,
        'has_archive'   => false,
        'menu_icon'     => 'dashicons-portfolio',
        'menu_position' => 5,
        'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'rewrite'       => [ 'slug' => 'lc-project', 'with_front' => false ],
    ]);
}
add_action( 'init', 'lc_register_project_cpt', 0 );

// ── CPT: lc_case_study ────────────────────────────────────
function lc_register_case_study_cpt() {
    register_post_type( 'lc_case_study', [
        'labels' => [
            'name'          => __( 'Case Studies',       'laws-codes' ),
            'singular_name' => __( 'Case Study',         'laws-codes' ),
            'add_new_item'  => __( 'Add New Case Study', 'laws-codes' ),
            'edit_item'     => __( 'Edit Case Study',    'laws-codes' ),
        ],
        'public'        => true,
        'show_in_rest'  => true,
        'has_archive'   => false,
        'menu_icon'     => 'dashicons-portfolio',
        'menu_position' => 6,
        'supports'      => [ 'title', 'excerpt', 'thumbnail', 'page-attributes', 'custom-fields' ],
        'rewrite'       => [ 'slug' => 'case-study', 'with_front' => false ],
    ]);
}
add_action( 'init', 'lc_register_case_study_cpt', 0 );

// ── Flush rewrites on theme switch ────────────────────────
add_action( 'after_switch_theme', function () {
    lc_register_project_cpt();
    lc_register_case_study_cpt();
    flush_rewrite_rules();
} );

// ── TAXONOMY: Project Category ────────────────────────────
function lc_register_project_tax() {
    register_taxonomy( 'lc_project_cat', 'lc_project', [
        'labels' => [
            'name'          => __( 'Project Categories', 'laws-codes' ),
            'singular_name' => __( 'Project Category',  'laws-codes' ),
        ],
        'public'       => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite'      => [ 'slug' => 'work-category' ],
    ]);
}
add_action( 'init', 'lc_register_project_tax' );

// ── PROJECT META FIELDS ───────────────────────────────────
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
        'meta_query'     => [[ 'key' => '_lc_featured', 'value' => '1' ]],
        'meta_key'       => '_lc_switcher_order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ]);

    $projects = [];

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();

            $projects[] = [
                'id'            => $id,
                'title'         => get_the_title(),
                'slug'          => get_post_field( 'post_name', $id ),
                'url'           => get_permalink( $id ),
                'industry'      => lc_project_field( $id, '_lc_industry' ),
                'categoryLabel' => lc_project_field( $id, '_lc_category_label' ),
                'bgColor'       => lc_project_field( $id, '_lc_bg_color' ) ?: '#0e0c2e',
                'kpi1Num'       => lc_project_field( $id, '_lc_kpi_1_num' ),
                'kpi1Label'     => lc_project_field( $id, '_lc_kpi_1_label' ),
                'kpi2Num'       => lc_project_field( $id, '_lc_kpi_2_num' ),
                'kpi2Label'     => lc_project_field( $id, '_lc_kpi_2_label' ),
                'techStack'     => array_map( 'trim', explode( ',', lc_project_field( $id, '_lc_tech_stack' ) ) ),
                'thumbnail'     => get_the_post_thumbnail_url( $id, 'lc-project-card' ),
            ];
        }
        wp_reset_postdata();
    }

    return $projects;
}

// ── CPT: lc_testimonial ───────────────────────────────────
function lc_register_testimonial_cpt() {
    register_post_type( 'lc_testimonial', [
        'labels' => [
            'name'          => __( 'Testimonials',        'laws-codes' ),
            'singular_name' => __( 'Testimonial',         'laws-codes' ),
            'add_new_item'  => __( 'Add New Testimonial', 'laws-codes' ),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-format-quote',
        'menu_position' => 7,
        'supports'      => [ 'title', 'editor', 'custom-fields' ],
    ]);

    foreach ( [ '_lc_testi_client', '_lc_testi_role', '_lc_testi_company', '_lc_testi_initials', '_lc_testi_project' ] as $key ) {
        register_post_meta( 'lc_testimonial', $key, [
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => 'string',
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

// ── CONTACT FORM AJAX ─────────────────────────────────────
function lc_handle_contact_form() {
    check_ajax_referer( 'lc_nonce', 'nonce' );

    $fields = [
        'first_name' => sanitize_text_field(     $_POST['first_name'] ?? '' ),
        'last_name'  => sanitize_text_field(     $_POST['last_name']  ?? '' ),
        'email'      => sanitize_email(          $_POST['email']      ?? '' ),
        'business'   => sanitize_text_field(     $_POST['business']   ?? '' ),
        'service'    => sanitize_text_field(     $_POST['service']    ?? '' ),
        'message'    => sanitize_textarea_field( $_POST['message']    ?? '' ),
    ];

    if ( empty( $fields['email'] ) || ! is_email( $fields['email'] ) ) {
        wp_send_json_error( [ 'message' => 'Please provide a valid email address.' ] );
    }

    if ( empty( $fields['first_name'] ) || empty( $fields['message'] ) ) {
        wp_send_json_error( [ 'message' => 'Please fill in all required fields.' ] );
    }

    $sent = wp_mail(
        get_option( 'admin_email' ),
        sprintf( 'New enquiry from %s %s', $fields['first_name'], $fields['last_name'] ),
        lc_build_contact_email( $fields ),
        [
            'Content-Type: text/html; charset=UTF-8',
            sprintf( 'Reply-To: %s <%s>', trim( $fields['first_name'] . ' ' . $fields['last_name'] ), $fields['email'] ),
        ]
    );

    if ( $sent ) {
        wp_send_json_success( [ 'message' => "Message sent. We'll be in touch within one business day." ] );
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

// ── QUIZ LEAD AJAX ────────────────────────────────────────
function lc_handle_quiz_lead() {
    check_ajax_referer( 'lc_nonce', 'nonce' );

    $result  = sanitize_text_field( $_POST['result']  ?? '' );
    $answers = array_map( 'sanitize_text_field', (array) ( $_POST['answers'] ?? [] ) );

    wp_insert_post([
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

// ── GLOBAL SEARCH AJAX ────────────────────────────────────
require_once LC_DIR . '/inc/search-ajax.php';

// ── STRIPE: Payment Intent (moved here from page-payment.php) ─────────────────
// Must live in functions.php so the AJAX handler is registered on every
// request — not just when the payment page template loads.
add_action( 'wp_ajax_nopriv_lc_create_payment_intent', 'lc_create_payment_intent' );
add_action( 'wp_ajax_lc_create_payment_intent',        'lc_create_payment_intent' );

function lc_create_payment_intent() {
    check_ajax_referer( 'lc_stripe_nonce', 'nonce' );

    if ( ! defined( 'STRIPE_SECRET_KEY' ) ) {
        wp_send_json_error( 'Stripe is not configured — STRIPE_SECRET_KEY missing from wp-config.php.' );
    }

    $autoload = get_template_directory() . '/vendor/autoload.php';
    if ( ! file_exists( $autoload ) ) {
        wp_send_json_error( 'Stripe PHP library not found. Run: composer require stripe/stripe-php in your theme folder.' );
    }
    require_once $autoload;

    \Stripe\Stripe::setApiKey( STRIPE_SECRET_KEY );

    $amount      = absint( $_POST['amount']      ?? 0 );
    $description = sanitize_text_field( $_POST['description'] ?? 'Laws & Codes — Project Payment' );
    $invoice_id  = sanitize_text_field( $_POST['invoice_id']  ?? '' );
    $email       = sanitize_email(      $_POST['email']       ?? '' );

    if ( $amount < 50 ) {
        wp_send_json_error( 'Amount too low: ' . $amount . ' cents (Stripe minimum is 50 cents / $0.50).' );
    }

    try {
        $intent = \Stripe\PaymentIntent::create( [
            'amount'      => $amount,
            'currency'    => 'usd',
            'description' => $description,
            'metadata'    => [
                'invoice_id'   => $invoice_id,
                'client_email' => $email,
            ],
            'receipt_email'             => $email ?: null,
            'automatic_payment_methods' => [ 'enabled' => true ],
        ] );

        wp_send_json_success( [
            'clientSecret' => $intent->client_secret,
            'intentId'     => $intent->id,
        ] );

    } catch ( \Stripe\Exception\ApiErrorException $e ) {
        wp_send_json_error( $e->getMessage() );
    }
}

// ── STRIPE: Payment notification email ────────────────────
function lc_notify_payment_received( $intent ) {
    $to      = get_option( 'admin_email' );
    $amount  = number_format( $intent->amount / 100, 2 );
    $invoice = $intent->metadata->invoice_id   ?? 'N/A';
    $client  = $intent->metadata->client_email ?? 'Unknown';
    $subject = "Payment received — \${$amount} · Invoice {$invoice}";
    $body    = "A payment of \${$amount} USD was received.\n\nInvoice: {$invoice}\nClient: {$client}\nStripe ID: {$intent->id}";
    wp_mail( $to, $subject, $body );
}

// ── STRIPE: Webhook (init hook for ?stripe_webhook=1) ─────
add_action( 'init', function () {
    if ( isset( $_GET['stripe_webhook'] ) && $_GET['stripe_webhook'] === '1' ) {
        if ( function_exists( 'lc_handle_stripe_webhook' ) ) {
            lc_handle_stripe_webhook();
            exit;
        }
    }
} );

// ── STRIPE: Webhook via REST API ──────────────────────────
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

    $autoload = get_template_directory() . '/vendor/autoload.php';
    if ( file_exists( $autoload ) ) {
        require_once $autoload;
    }

    \Stripe\Stripe::setApiKey( STRIPE_SECRET_KEY );

    try {
        $event = \Stripe\Webhook::constructEvent(
            $request->get_body(),
            $request->get_header( 'stripe-signature' ),
            STRIPE_WEBHOOK_SECRET
        );
    } catch ( \Exception $e ) {
        return new WP_REST_Response( [ 'error' => $e->getMessage() ], 400 );
    }

    if ( $event->type === 'payment_intent.succeeded' ) {
        lc_notify_payment_received( $event->data->object );
    }

    return new WP_REST_Response( [ 'received' => true ], 200 );
}

// ── STRIPE: Payment URL helper ────────────────────────────
function lc_payment_url( string $invoice_id, int $amount_usd, string $description = '' ): string {
    $page = get_page_by_path( 'payment' );
    if ( ! $page ) return home_url( '/payment/' );

    return add_query_arg( [
        'invoice' => rawurlencode( $invoice_id ),
        'amount'  => $amount_usd * 100,
        'desc'    => rawurlencode( $description ?: "Invoice {$invoice_id}" ),
    ], get_permalink( $page ) );
}

// ── WIDGET AREAS ──────────────────────────────────────────
add_action( 'widgets_init', function () {
    register_sidebar([
        'name'          => __( 'Footer Widget Area', 'laws-codes' ),
        'id'            => 'footer-1',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
});

// ── CLEAN UP WP HEAD ──────────────────────────────────────
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

// ── MEDIA LIBRARY CUSTOM SIZES ────────────────────────────
add_filter( 'image_size_names_choose', function ( $sizes ) {
    return array_merge( $sizes, [
        'lc-project-thumb' => __( 'Project Thumbnail', 'laws-codes' ),
        'lc-project-hero'  => __( 'Project Hero',      'laws-codes' ),
        'lc-project-card'  => __( 'Project Card',      'laws-codes' ),
    ]);
});

// ── SEO & EXCERPT ─────────────────────────────────────────
add_filter( 'document_title_separator', fn() => '—' );
add_filter( 'excerpt_length',           fn() => 28 );
add_filter( 'excerpt_more',             fn() => '...' );