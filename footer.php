</main>

<footer class="site-footer" role="contentinfo">
  <div class="footer-grid">
    <div>
      <div class="footer-brand-name"><?php bloginfo('name'); ?></div>
      <p class="footer-brand-sub">
        Coding digital legacies — custom web experiences for businesses that deserve to stand out.
      </p>
    </div>
    <div>
      <div class="footer-col-title">Pages</div>
      <?php
      $pages = ['Work'=>'/work/','Services'=>'/services/','Process'=>'/process/','About'=>'/about/','Contact'=>'/contact/'];
      foreach ( $pages as $label => $path ) :
      ?>
        <a href="<?php echo esc_url( home_url( $path ) ); ?>" class="footer-link">
          <?php echo esc_html( $label ); ?>
        </a>
      <?php endforeach; ?>
    </div>
    <div>
      <div class="footer-col-title">Services</div>
      <?php
      $services = ['Website design','E-commerce','SEO & maintenance','Branding'];
      foreach ( $services as $svc ) :
      ?>
        <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="footer-link">
          <?php echo esc_html( $svc ); ?>
        </a>
      <?php endforeach; ?>
    </div>
    <div>
      <div class="footer-col-title">Connect</div>
      <a href="https://instagram.com/lawsandcodes" class="footer-link" target="_blank" rel="noopener">Instagram</a>
      <a href="https://github.com/"                 class="footer-link" target="_blank" rel="noopener">GitHub</a>
      <a href="https://linkedin.com/"               class="footer-link" target="_blank" rel="noopener">LinkedIn</a>
    </div>
  </div>
  <div class="footer-bottom">
    <span class="footer-copy">
      &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.
    </span>
    <span class="footer-copy">Payments secured by Stripe</span>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
