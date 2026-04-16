<?php
/**
 * Laws & Codes — footer.php
 * LC_Footer_Walker is defined in functions.php (loads before this template).
 */
?>
</main>

<footer class="site-footer" role="contentinfo">
  <div class="footer-grid">

    <!-- Brand -->
    <div>
      <div class="footer-brand-name">Laws & Codes</div>
      <p class="footer-brand-sub">
        Coding digital legacies — custom web experiences for businesses that deserve to stand out.
      </p>
    </div>

    <!-- Pages -->
    <div>
      <div class="footer-col-title">Pages</div>
      <?php if ( has_nav_menu('footer') ) :
        wp_nav_menu([
          'theme_location' => 'footer',
          'container'      => false,
          'menu_class'     => 'footer-nav',
          'depth'          => 1,
          'walker'         => new LC_Footer_Walker(),
        ]);
      else : ?>
        <?php foreach ( ['Work'=>'/work/','Services'=>'/services/','Process'=>'/process/','About'=>'/about/','Contact'=>'/contact/'] as $label => $path ) : ?>
          <a href="<?php echo esc_url( home_url( $path ) ); ?>" class="footer-link"><?php echo esc_html( $label ); ?></a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Services -->
    <div>
      <div class="footer-col-title">Services</div>
      <?php foreach ( ['Website design','E-commerce','SEO & maintenance','Branding'] as $svc ) : ?>
        <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="footer-link"><?php echo esc_html( $svc ); ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Connect -->
    <div>
      <div class="footer-col-title">Connect</div>
      <?php if ( has_nav_menu('social') ) :
        wp_nav_menu([
          'theme_location' => 'social',
          'container'      => false,
          'menu_class'     => 'footer-nav',
          'depth'          => 1,
          'walker'         => new LC_Footer_Walker(),
        ]);
      else : ?>
        <a href="https://instagram.com/lawsandcodes" class="footer-link" target="_blank" rel="noopener">Instagram</a>
        <a href="https://github.com/"                class="footer-link" target="_blank" rel="noopener">GitHub</a>
        <a href="https://linkedin.com/"              class="footer-link" target="_blank" rel="noopener">LinkedIn</a>
      <?php endif; ?>
    </div>

  </div>

  <div class="footer-bottom">
    <span class="footer-copy">&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</span>
    <span class="footer-copy">Payments secured by Stripe</span>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>