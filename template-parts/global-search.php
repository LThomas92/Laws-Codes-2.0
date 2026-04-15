<?php
/**
 * Global Search Component
 * template-parts/global-search.php
 */

add_action( 'wp_footer', 'lc_render_search_overlay', 5 );

if ( ! function_exists( 'lc_render_search_overlay' ) ) :
function lc_render_search_overlay() {
    static $rendered = false;
    if ( $rendered ) return;
    $rendered = true;

    $quick_links = [
        'Work'     => home_url( '/work/' ),
        'Services' => home_url( '/services/' ),
        'Process'  => home_url( '/process/' ),
        'About'    => home_url( '/about/' ),
        'Contact'  => home_url( '/contact/' ),
    ];

    $recent_cs = get_posts( [
        'post_type'      => 'lc_case_study',
        'posts_per_page' => 3,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ] );
    ?>
    <div class="global-search-overlay" id="global-search-overlay"
         role="dialog" aria-label="Site search" aria-modal="true" aria-hidden="true">

        <div class="global-search-overlay__backdrop" id="global-search-backdrop"></div>

        <div class="global-search-overlay__panel">

            <div class="global-search-input-row">
                <svg class="global-search-input-row__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    class="global-search-input-row__input"
                    id="global-search-input"
                    type="search"
                    placeholder="Search projects, pages, services..."
                    autocomplete="off"
                    aria-label="Search"
                    spellcheck="false"
                >
                <div class="global-search-input-row__kbd" aria-hidden="true">ESC</div>
                <button class="global-search-input-row__close" id="global-search-close" type="button" aria-label="Close search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Single scrollable body — JS controls which panel is visible via data-state -->
            <div class="gs-body" id="gs-body" data-state="default">

                <!-- Loading -->
                <div class="gs-panel gs-panel--loading" id="gs-loading">
                    <div class="global-search-spinner" aria-hidden="true"></div>
                    <span>Searching...</span>
                </div>

                <!-- Empty -->
                <div class="gs-panel gs-panel--empty" id="gs-empty">
                    <span class="gs-empty-icon" aria-hidden="true">&#9675;</span>
                    <p>No results for "<strong id="gs-empty-term"></strong>"</p>
                    <span>Try a different keyword or browse the site.</span>
                </div>

                <!-- Results -->
                <div class="gs-panel gs-panel--results" id="gs-results-list" role="listbox"></div>

                <!-- Default / idle -->
                <div class="gs-panel gs-panel--default" id="gs-default">
                    <div class="gs-default-section">
                        <span class="gs-default-label">Quick links</span>
                        <div class="gs-default-links">
                            <?php foreach ( $quick_links as $label => $url ) : ?>
                            <a class="gs-default-link" href="<?php echo esc_url( $url ); ?>">
                                <span><?php echo esc_html( $label ); ?></span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                    <polyline points="12 5 19 12 12 19"/>
                                </svg>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ( $recent_cs ) : ?>
                    <div class="gs-default-section">
                        <span class="gs-default-label">Recent work</span>
                        <?php foreach ( $recent_cs as $p ) :
                            $bg  = get_field( 'cs_bg_color', $p->ID ) ?: '#0e0c2e';
                            $ini = get_field( 'cs_initials',  $p->ID ) ?: '';
                            $ind = get_field( 'cs_industry',  $p->ID ) ?: '';
                        ?>
                        <a class="gs-result-item" href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>">
                            <div class="gs-result-item__thumb" style="background:<?php echo esc_attr( $bg ); ?>" aria-hidden="true">
                                <?php echo esc_html( $ini ); ?>
                            </div>
                            <div class="gs-result-item__body">
                                <div class="gs-result-item__title"><?php echo esc_html( $p->post_title ); ?></div>
                                <div class="gs-result-item__meta"><?php echo esc_html( $ind ); ?></div>
                            </div>
                            <div class="gs-result-item__type" aria-hidden="true">Project</div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div><!-- /.gs-body -->

            <div class="global-search-footer" aria-hidden="true">
                <span><kbd>&uarr;</kbd><kbd>&darr;</kbd> navigate</span>
                <span><kbd>&crarr;</kbd> open</span>
                <span><kbd>ESC</kbd> close</span>
            </div>
        </div>
    </div>
    <?php
}
endif;

add_action( 'wp_footer', 'lc_localise_search_config', 1 );

if ( ! function_exists( 'lc_localise_search_config' ) ) :
function lc_localise_search_config() {
    static $done = false;
    if ( $done ) return;
    $done = true;

    $config = [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'lc_search_nonce' ),
        'homeUrl' => home_url( '/' ),
    ];

    $ok = wp_localize_script( 'lawscodes-search', 'LC_SEARCH', $config );
    if ( ! $ok ) {
        echo '<script>var LC_SEARCH = ' . wp_json_encode( $config ) . ';</script>' . "\n";
    }
}
endif;
?>

<button
    class="global-search-trigger"
    id="global-search-trigger"
    aria-label="Search site"
    aria-expanded="false"
    aria-controls="global-search-overlay"
    type="button"
>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <circle cx="11" cy="11" r="8"/>
        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <span class="global-search-trigger__label">Search</span>
</button>