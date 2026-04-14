<?php
/**
 * Template Name: Terms of Service
 *
 * ACF Fields (same group as Privacy Policy — "Legal Pages"):
 *   - legal_last_updated  (date_picker)
 *   - legal_content       (wysiwyg)  — override static content if needed
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
      <h1 class="legal-hero__h1">Terms of Service</h1>
      <p class="legal-hero__meta">Last updated: <?php echo esc_html( $last_updated ); ?></p>
    </div>
  </section>

  <div class="legal-body section">
    <div class="legal-body__inner">

      <?php if ( $custom_content ) : ?>
        <?php echo wp_kses_post( $custom_content ); ?>
      <?php else : ?>

      <h2>1. Acceptance of Terms</h2>
      <p>By accessing <?php echo esc_html( home_url() ); ?> or engaging Laws &amp; Codes for services, you agree to be bound by these Terms of Service ("Terms"). If you do not agree, please do not use the Site or our services.</p>

      <h2>2. Services</h2>
      <p>Laws &amp; Codes provides custom web design, web development, e-commerce builds, branding, and related digital services ("Services") as agreed in a separate project proposal or statement of work ("SOW").</p>

      <h2>3. Project Proposals & Scope</h2>
      <p>Each project begins with a written proposal or SOW outlining scope, deliverables, timeline, and price. Work outside the agreed scope is subject to a change-order and additional fees. Verbal agreements do not modify the written SOW.</p>

      <h2>4. Payment Terms</h2>
      <ul>
        <li><strong>50% deposit</strong> is due before work begins.</li>
        <li><strong>50% balance</strong> is due upon project completion before final files or launch access are delivered.</li>
        <li>Invoices are processed via Stripe (credit card, debit, or ACH).</li>
        <li>Invoices unpaid 14 days past due accrue interest at 1.5% per month.</li>
        <li>Laws &amp; Codes reserves the right to suspend or withhold deliverables until outstanding balances are settled.</li>
      </ul>

      <h2>5. Revisions</h2>
      <p>Each project phase includes two (2) rounds of revisions unless otherwise specified in the SOW. Additional revision rounds are billed at the hourly rate on file.</p>

      <h2>6. Client Responsibilities</h2>
      <p>You agree to: provide necessary content, credentials, and feedback in a timely manner; obtain all licenses for content you supply; and designate a single point of contact for approvals. Delays caused by client inaction may affect the agreed timeline.</p>

      <h2>7. Intellectual Property</h2>
      <p>Upon receipt of full payment, you own all custom code and design assets created specifically for your project. Laws &amp; Codes retains the right to display the completed work in its portfolio. Third-party components (WordPress, plugins, stock assets) remain under their respective licenses.</p>

      <h2>8. Confidentiality</h2>
      <p>Both parties agree to keep confidential all non-public information shared during the project. This obligation survives termination of the engagement for three (3) years.</p>

      <h2>9. Cancellation</h2>
      <p>Either party may cancel the project with 14 days' written notice. The deposit is non-refundable. Work completed to date will be invoiced at the prorated contract rate. Laws &amp; Codes will deliver all work completed through the cancellation date upon settlement of the final invoice.</p>

      <h2>10. Limitation of Liability</h2>
      <p>To the fullest extent permitted by law, Laws &amp; Codes's liability for any claim arising from services is limited to the total fees paid for the project in question. We are not liable for indirect, incidental, or consequential damages.</p>

      <h2>11. Warranties</h2>
      <p>We warrant that work will be performed in a professional manner consistent with industry standards. We do not warrant that the Site will be error-free or uninterrupted after handoff.</p>

      <h2>12. Governing Law</h2>
      <p>These Terms are governed by the laws of the State of New York, without regard to conflict-of-law provisions. Any dispute shall be resolved exclusively in the state or federal courts located in New York County, New York.</p>

      <h2>13. Changes to Terms</h2>
      <p>We reserve the right to update these Terms. Continued use of the Site or engagement of our services after changes constitutes acceptance.</p>

      <h2>14. Contact</h2>
      <p>Questions? Email <a href="mailto:hello@lawscodes.com">hello@lawscodes.com</a>.</p>

      <?php endif; ?>

    </div>
  </div>

</main>

<?php get_footer(); ?>
