<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-nav" role="banner">

  <!-- Brand -->
  <a href="<?php echo esc_url( home_url('/') ); ?>" class="site-nav__brand" aria-label="<?php bloginfo('name'); ?> — Home">
  
    <span class="site-nav__brand-name">
      <?php
      $logo = get_template_directory_uri() . '/img/logo.png';
      ?>
      <img src="<?php echo esc_url( $logo ); ?>" alt="<?php bloginfo('name'); ?>" height="22">
    </span>
  </a>

  <!-- Desktop links -->
  <nav class="site-nav__links" aria-label="Primary navigation">
    <?php
    wp_nav_menu([
      'theme_location' => 'primary',
      'container'      => false,
      'items_wrap'     => '%3$s',
      'walker'         => new class extends Walker_Nav_Menu {
        public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
          $current = $item->current ? ' aria-current="page"' : '';
          $output .= '<a href="' . esc_url( $item->url ) . '"' . $current . '>'
                   . esc_html( $item->title ) . '</a>';
        }
      },
      'fallback_cb' => function() {
        $links = [
          'Work'     => '/work/',
          'Services' => '/services/',
          'Process'  => '/process/',
          'About'    => '/about/',
        ];
        foreach ( $links as $label => $path ) {
          echo '<a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $label ) . '</a>';
        }
      },
    ]);
    ?>
  </nav>

  <!-- Right side -->
  <div class="site-nav__right">
    <span class="site-nav__slots">2 slots open</span>
    <a href="<?php echo esc_url( home_url('/contact/') ); ?>" class="site-nav__cta">
      Book a call
    </a>
    <?php get_template_part('template-parts/global-search'); ?>
    <!-- Hamburger -->
    <button
      class="site-nav__hamburger"
      aria-label="Open menu"
      aria-expanded="false"
      aria-controls="mobile-menu"
    >
      <span class="site-nav__bar"></span>
      <span class="site-nav__bar"></span>
      <span class="site-nav__bar"></span>
    </button>
  </div>

</header>

<!-- Mobile menu overlay -->
<div class="mobile-menu" id="mobile-menu" aria-hidden="true" role="dialog" aria-label="Mobile navigation">

  <div class="mobile-menu__inner">

    <!-- Close row -->
    <div class="mobile-menu__top">
      <a href="<?php echo esc_url( home_url('/') ); ?>" class="mobile-menu__brand">
        Laws &amp; Codes
      </a>
      <button class="mobile-menu__close" aria-label="Close menu">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
          <line x1="1" y1="1" x2="17" y2="17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="17" y1="1" x2="1"  y2="17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </button>
    </div>

    <!-- Nav links -->
    <nav class="mobile-menu__nav" aria-label="Mobile navigation">
      <?php
      wp_nav_menu([
        'theme_location' => 'primary',
        'container'      => false,
        'items_wrap'     => '%3$s',
        'walker'         => new class extends Walker_Nav_Menu {
          public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
            $current = $item->current ? ' aria-current="page"' : '';
            $output .= '<a class="mobile-menu__link" href="' . esc_url( $item->url ) . '"' . $current . '>'
                     . esc_html( $item->title )
                     . '<span class="mobile-menu__arrow" aria-hidden="true">→</span>'
                     . '</a>';
          }
        },
        'fallback_cb' => function() {
          $links = [
            'Work'     => '/work/',
            'Services' => '/services/',
            'Process'  => '/process/',
            'About'    => '/about/',
            'Contact'  => '/contact/',
          ];
          foreach ( $links as $label => $path ) {
            echo '<a class="mobile-menu__link" href="' . esc_url( home_url( $path ) ) . '">'
               . esc_html( $label )
               . '<span class="mobile-menu__arrow" aria-hidden="true">→</span>'
               . '</a>';
          }
        },
      ]);
      ?>
    </nav>

    <!-- Bottom CTA -->
    <div class="mobile-menu__footer">
      <a href="<?php echo esc_url( home_url('/contact/') ); ?>" class="mobile-menu__cta">
        Book a discovery call
      </a>
      <span class="mobile-menu__slots">◆ 2 project slots open this quarter ◆</span>
      <a href="mailto:hello@lawscodes.com" class="mobile-menu__email">hello@lawscodes.com</a>
    </div>

  </div>

  <!-- Backdrop -->
  <div class="mobile-menu__backdrop" aria-hidden="true"></div>

</div>

<script>
(function () {
  const hamburger = document.querySelector('.site-nav__hamburger');
  const closeBtn  = document.querySelector('.mobile-menu__close');
  const menu      = document.getElementById('mobile-menu');
  const backdrop  = document.querySelector('.mobile-menu__backdrop');
  const body      = document.body;

  function openMenu() {
    menu.classList.add('is-open');
    menu.setAttribute('aria-hidden', 'false');
    hamburger.setAttribute('aria-expanded', 'true');
    hamburger.classList.add('is-active');
    body.classList.add('menu-open');
    // Focus first link for accessibility
    const firstLink = menu.querySelector('.mobile-menu__link');
    if (firstLink) setTimeout(() => firstLink.focus(), 50);
  }

  function closeMenu() {
    menu.classList.remove('is-open');
    menu.setAttribute('aria-hidden', 'true');
    hamburger.setAttribute('aria-expanded', 'false');
    hamburger.classList.remove('is-active');
    body.classList.remove('menu-open');
    hamburger.focus();
  }

  hamburger.addEventListener('click', openMenu);
  closeBtn.addEventListener('click', closeMenu);
  backdrop.addEventListener('click', closeMenu);

  // Close on Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && menu.classList.contains('is-open')) closeMenu();
  });

  // Close if a mobile link is clicked (SPA-style nav or same-page)
  menu.querySelectorAll('.mobile-menu__link').forEach(function (link) {
    link.addEventListener('click', function () {
      // slight delay so the page starts navigating before menu closes
      setTimeout(closeMenu, 120);
    });
  });
})();
</script>

<div id="page-content">