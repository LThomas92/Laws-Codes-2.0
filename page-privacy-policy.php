<?php
/**
 * Template Name: Privacy Policy
 *
 * ACF Fields Required (ACF > Field Groups > "Legal Pages"):
 *   - legal_last_updated  (date_picker)  — shown in header
 *   - legal_content       (wysiwyg)      — main body, or leave blank to use
 *                                          the static content below as fallback
 *
 * Usage: Create a page titled "Privacy Policy", apply this template.
 * The fallback copy below covers the core obligations for a web studio.
 * Have your attorney review before publishing.
 */

get_header();

$last_updated = get_field('legal_last_updated')
    ? date_i18n( get_option('date_format'), strtotime( get_field('legal_last_updated') ) )
    : date_i18n( get_option('date_format') );

$custom_content = get_field('legal_content');
?>

<main class="lc-legal" id="main">

  <section class="legal-hero">
    <div class="legal-hero__inner">
      <div class="legal-hero__tag">
        <span class="legal-hero__dash"></span>Legal
      </div>
      <h1 class="legal-hero__h1">Privacy Policy</h1>
      <p class="legal-hero__meta">Last updated: <?php echo esc_html( $last_updated ); ?></p>
    </div>
  </section>

  <div class="legal-body section">
    <div class="legal-body__inner">

      <?php if ( $custom_content ) : ?>
        <?php echo wp_kses_post( $custom_content ); ?>
      <?php else : ?>

      <!-- ── FALLBACK STATIC CONTENT ─────────────────────────────────────── -->
      <!-- Replace / augment this via the ACF wysiwyg field "legal_content"  -->

      <h2>1. Introduction</h2>
      <p>Laws &amp; Codes ("we," "us," or "our") operates <?php echo esc_html( home_url() ); ?> (the "Site"). This Privacy Policy describes how we collect, use, and share information when you visit our Site or engage our services.</p>

      <h2>2. Information We Collect</h2>
      <h3>Information you provide directly</h3>
      <ul>
        <li>Contact form submissions (name, email address, business name, project details).</li>
        <li>Payment information processed through Stripe. We never store your full card number — Stripe handles all payment data under their own <a href="https://stripe.com/privacy" target="_blank" rel="noopener">Privacy Policy</a>.</li>
        <li>Email correspondence.</li>
      </ul>
      <h3>Information collected automatically</h3>
      <ul>
        <li>Log data (IP address, browser type, pages visited, referring URLs, timestamps).</li>
        <li>Cookies and similar tracking technologies (see Section 6).</li>
        <li>Analytics data via Google Analytics or similar tools (aggregated, anonymized where possible).</li>
      </ul>

      <h2>3. How We Use Your Information</h2>
      <p>We use the information we collect to:</p>
      <ul>
        <li>Respond to inquiries and deliver the services you request.</li>
        <li>Process payments and send invoices.</li>
        <li>Improve our website and service offering.</li>
        <li>Comply with legal obligations.</li>
        <li>Send project-related communications (we do not send unsolicited marketing email).</li>
      </ul>

      <h2>4. Legal Basis for Processing (GDPR)</h2>
      <p>If you are located in the European Economic Area, our legal bases for processing personal data are: (a) your consent; (b) performance of a contract; (c) compliance with legal obligations; and (d) our legitimate interests in operating and improving the Site.</p>

      <h2>5. Sharing Your Information</h2>
      <p>We do not sell your personal information. We may share it with:</p>
      <ul>
        <li><strong>Stripe</strong> — payment processing.</li>
        <li><strong>Google Analytics</strong> — site analytics (anonymized).</li>
        <li><strong>Service providers</strong> acting on our behalf who are contractually bound to keep data confidential.</li>
        <li><strong>Law enforcement</strong> when required by law.</li>
      </ul>

      <h2>6. Cookies</h2>
      <p>We use strictly-necessary cookies (session, security) and, with your consent, analytics cookies. You may decline non-essential cookies via your browser settings or our cookie banner. Disabling cookies may limit some Site functionality.</p>

      <h2>7. Data Retention</h2>
      <p>We retain contact form submissions and project-related correspondence for up to three (3) years after the conclusion of a project, or as required by applicable law. Payment records are retained for seven (7) years for tax and accounting purposes.</p>

      <h2>8. Your Rights</h2>
      <p>Depending on your jurisdiction, you may have the right to access, correct, delete, or port your personal data, or to withdraw consent at any time. To exercise these rights, email us at <a href="mailto:hello@lawscodes.com">hello@lawscodes.com</a>. We will respond within 30 days.</p>

      <h2>9. Children's Privacy</h2>
      <p>Our services are not directed to individuals under 16 years of age. We do not knowingly collect personal data from minors.</p>

      <h2>10. Third-Party Links</h2>
      <p>Our Site may contain links to third-party websites. We are not responsible for the privacy practices or content of those sites.</p>

      <h2>11. Changes to This Policy</h2>
      <p>We may update this Policy from time to time. The "Last updated" date at the top of this page reflects the most recent revision. Continued use of the Site after changes constitutes acceptance of the revised Policy.</p>

      <h2>12. Contact</h2>
      <p>Questions about this Policy? Contact us at:<br>
      <strong>Laws &amp; Codes</strong><br>
      New York, NY<br>
      <a href="mailto:hello@lawscodes.com">hello@lawscodes.com</a></p>

      <?php endif; ?>

    </div>
  </div>

</main>

<?php get_footer(); ?>
