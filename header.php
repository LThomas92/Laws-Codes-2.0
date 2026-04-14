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
  <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-brand" aria-label="<?php bloginfo('name'); ?> — Home">
    <div class="nav-logo-mark">
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
        <path d="M2 4h12M2 8h8M2 12h10" stroke="#E8CCB2" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
    </div>
    <span class="nav-brand-name">
      <img src="<?php echo get_template_directory_uri(); ?>/img/logo.png" alt="Laws & Codes logo">
    </span>
  </a>

  <nav class="nav-links" aria-label="Primary navigation">
    <?php
    wp_nav_menu([
      'theme_location' => 'primary',
      'container'      => false,
      'items_wrap'     => '%3$s',
      'walker'         => new class extends Walker_Nav_Menu {
        public function start_el(&$output,$item,$depth=0,$args=null,$id=0){
          $output .= '<a href="'.esc_url($item->url).'"'
            .($item->current?' aria-current="page"':'').'>'
            .esc_html($item->title).'</a>';
        }
      },
      'fallback_cb'    => function() {
        foreach ( ['Work'=>'/work/','Services'=>'/services/','Process'=>'/process/','About'=>'/about/'] as $l=>$u ) {
          echo '<a href="'.esc_url(home_url($u)).'">'.esc_html($l).'</a>';
        }
      },
    ]);
    ?>
  </nav>

  <div class="nav-right">
    <span class="nav-slots-badge">2 slots open</span>
    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="nav-cta-btn">Book a call</a>
    <button class="nav-hamburger" aria-label="Open menu" aria-expanded="false" aria-controls="nav-mobile-menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<nav class="nav-mobile-menu" id="nav-mobile-menu" aria-label="Mobile navigation">
  <?php
  wp_nav_menu([
    'theme_location'=>'primary','container'=>false,'items_wrap'=>'%3$s',
    'walker'=> new class extends Walker_Nav_Menu {
      public function start_el(&$output,$item,$depth=0,$args=null,$id=0){
        $output.='<a href="'.esc_url($item->url).'">'.esc_html($item->title).'</a>';
      }
    },
    'fallback_cb'=>function(){
      foreach(['Work'=>'/work/','Services'=>'/services/','Process'=>'/process/','About'=>'/about/','Contact'=>'/contact/'] as $l=>$u)
        echo '<a href="'.esc_url(home_url($u)).'">'.esc_html($l).'</a>';
    },
  ]);
  ?>
</nav>

<main id="main-content" role="main">
