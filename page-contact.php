<?php
/**
 * Laws & Codes — page-contact.php
 * Contact page template
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>

<div class="contact-layout">

  <!-- LEFT PANEL -->
  <div class="contact-left">
    <div>
      <div class="contact-kicker">Get in touch</div>
      <h1 class="contact-heading">Let's build something you're proud of.</h1>
      <p class="contact-subtext">
        Tell us about your project. We'll respond within one business day with
        honest feedback and a clear path forward — no pressure, no jargon.
      </p>

      <div class="contact-detail">
        <div class="contact-detail-icon">
          <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <span class="contact-detail-text">hello@lawscodes.com</span>
      </div>

      <div class="contact-detail">
        <div class="contact-detail-icon">
          <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <span class="contact-detail-text">New York, NY &middot; Available worldwide</span>
      </div>

      <div class="contact-detail">
        <div class="contact-detail-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <span class="contact-detail-text">Responds within 1 business day</span>
      </div>
    </div>

    <!-- Stripe badge -->
    <div class="stripe-badge">
      <div class="stripe-icon-wrap">
        <svg viewBox="0 0 24 24">
          <path d="M10.6 5.4C7.5 5.4 5.4 7 5.4 9.5c0 3.5 4.8 4 4.8 6 0 1-.8 1.6-2.2 1.6-2 0-3.4-.9-3.4-.9l-.6 2.8s1.6.8 3.8.8c3.4 0 5.6-1.7 5.6-4.4 0-3.6-4.8-4.2-4.8-6 0-.8.7-1.4 2-1.4 1.6 0 2.9.6 2.9.6l.6-2.7s-1.3-.6-3.5-.6z"/>
        </svg>
      </div>
      <div class="stripe-text">
        <p>Payments via Stripe</p>
        <span>Credit card, debit &amp; ACH — secure payment link with every invoice. 50% upfront, 50% on delivery.</span>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL — FORM -->
  <div class="contact-right">
    <h2 class="contact-form-title">Tell us about your project</h2>

    <?php if ( class_exists( 'GFForms' ) ) : ?>
      <?php
      /**
       * Gravity Forms contact form.
       * Change the ID below to match the form you created in Forms > New Form.
       * Recommended fields: First Name, Last Name, Email, Business Name,
       * Service (dropdown), Project Details (paragraph), Submit button.
       *
       * To pre-select a service from the quiz result, map the ?service= URL
       * parameter to the Service field via GF > Notifications or a GF hook.
       */
      $gf_contact_id = (int) apply_filters( 'lc_gf_contact_form_id', 1 );
      echo do_shortcode( '[gravityforms id="' . $gf_contact_id . '" ajax="true" tabindex="1"]' );
      ?>
    <?php else : ?>

      <div class="js-form-status" role="alert" aria-live="polite"></div>

      <form id="lc-contact-form" novalidate>
        <?php wp_nonce_field( 'lc_nonce', 'lc_form_nonce' ); ?>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="lc-first-name">First name</label>
            <input class="form-input" type="text" id="lc-first-name" name="first_name"
                   placeholder="Lawrence" autocomplete="given-name" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="lc-last-name">Last name</label>
            <input class="form-input" type="text" id="lc-last-name" name="last_name"
                   placeholder="Thomas" autocomplete="family-name" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="lc-email">Email address</label>
          <input class="form-input" type="email" id="lc-email" name="email"
                 placeholder="you@yourbusiness.com" autocomplete="email" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="lc-business">Business name</label>
          <input class="form-input" type="text" id="lc-business" name="business"
                 placeholder="Your Company" autocomplete="organization">
        </div>

        <div class="form-group">
          <label class="form-label" for="lc-service">Service needed</label>
          <select class="form-select" id="lc-service" name="service">
            <?php
            $quiz_result = sanitize_text_field( $_GET['service'] ?? '' );
            $services = [
              'Custom website design',
              'E-commerce development',
              'SEO & maintenance',
              'Branding & logo design',
              "Full package — let's talk",
            ];
            foreach ( $services as $svc ) :
              $selected = selected( $quiz_result, $svc, false );
            ?>
              <option value="<?php echo esc_attr( $svc ); ?>" <?php echo $selected; ?>>
                <?php echo esc_html( $svc ); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="lc-message">Project details</label>
          <textarea class="form-textarea" id="lc-message" name="message"
                    placeholder="Tell us about your goals, timeline, and any inspiration you have in mind..."
                    required></textarea>
        </div>

        <button type="submit" class="form-submit">Send message</button>
      </form>

    <?php endif; ?>
  </div>

</div>

<?php get_footer(); ?>
