<?php
/**
 * Template Name: Payment
 *
 * Stripe Integration — Deposit / Invoice Payment Page
 * ────────────────────────────────────────────────────
 * Setup:
 *  1. Install & activate the WP library via Composer or manually:
 *       composer require stripe/stripe-php
 *     or drop /vendor/stripe into your theme root.
 *
 *  2. Add to wp-config.php (NEVER commit real keys):
 *       define( 'STRIPE_SECRET_KEY',      'sk_live_...' );
 *       define( 'STRIPE_PUBLISHABLE_KEY', 'pk_live_...' );
 *       define( 'STRIPE_WEBHOOK_SECRET',  'whsec_...' );
 *
 *  3. In Stripe Dashboard → Developers → Webhooks:
 *       Add endpoint: https://yourdomain.com/?stripe_webhook=1
 *       Events to listen: payment_intent.succeeded, payment_intent.payment_failed
 *
 *  4. Create a page titled "Payment", apply this template.
 *     Link clients to: https://yourdomain.com/payment/?invoice=INV-001&amount=120000&desc=50%25+Deposit+Project
 *     (amount is in CENTS — e.g. 120000 = $1,200.00)
 *
 * ACF Fields (ACF > Field Groups > "Payment Page"):
 *   - payment_terms_note  (textarea)   short blurb shown under the form
 *   - stripe_logo_visible (true_false) show/hide Stripe badge
 */

// ── Handle Stripe webhook (must run before any output) ─────────────────────
if ( isset( $_GET['stripe_webhook'] ) ) {
    lc_handle_stripe_webhook();
    exit;
}

// ── Process PaymentIntent creation via AJAX ────────────────────────────────
add_action( 'wp_ajax_nopriv_lc_create_payment_intent', 'lc_create_payment_intent' );
add_action( 'wp_ajax_lc_create_payment_intent',        'lc_create_payment_intent' );

function lc_create_payment_intent() {
    check_ajax_referer( 'lc_stripe_nonce', 'nonce' );

    if ( ! defined( 'STRIPE_SECRET_KEY' ) ) {
        wp_send_json_error( 'Stripe is not configured.' );
    }

    require_once get_template_directory() . '/vendor/autoload.php';
    \Stripe\Stripe::setApiKey( STRIPE_SECRET_KEY );

    $amount      = absint( $_POST['amount'] ?? 0 );
    $description = sanitize_text_field( $_POST['description'] ?? 'Laws & Codes — Project Payment' );
    $invoice_id  = sanitize_text_field( $_POST['invoice_id'] ?? '' );
    $email       = sanitize_email( $_POST['email'] ?? '' );

    if ( $amount < 50 ) { // Stripe minimum is $0.50
        wp_send_json_error( 'Amount too low.' );
    }

    try {
        $intent = \Stripe\PaymentIntent::create([
            'amount'      => $amount,
            'currency'    => 'usd',
            'description' => $description,
            'metadata'    => [
                'invoice_id'  => $invoice_id,
                'client_email' => $email,
            ],
            'receipt_email'        => $email ?: null,
            'automatic_payment_methods' => [ 'enabled' => true ],
        ]);

        wp_send_json_success([
            'clientSecret' => $intent->client_secret,
            'intentId'     => $intent->id,
        ]);
    } catch ( \Stripe\Exception\ApiErrorException $e ) {
        wp_send_json_error( $e->getMessage() );
    }
}

function lc_handle_stripe_webhook() {
    if ( ! defined( 'STRIPE_SECRET_KEY' ) ) { http_response_code( 400 ); exit; }

    require_once get_template_directory() . '/vendor/autoload.php';
    \Stripe\Stripe::setApiKey( STRIPE_SECRET_KEY );

    $payload = @file_get_contents( 'php://input' );
    $sig     = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    try {
        $event = \Stripe\Webhook::constructEvent( $payload, $sig, STRIPE_WEBHOOK_SECRET );
    } catch ( \Exception $e ) {
        http_response_code( 400 );
        exit;
    }

    switch ( $event->type ) {
        case 'payment_intent.succeeded':
            $intent = $event->data->object;
            // Log or email notification:
            lc_notify_payment_received( $intent );
            break;
        case 'payment_intent.payment_failed':
            // Optionally notify client
            break;
    }

    http_response_code( 200 );
    echo json_encode([ 'received' => true ]);
    exit;
}

function lc_notify_payment_received( $intent ) {
    $to      = get_option('admin_email');
    $amount  = number_format( $intent->amount / 100, 2 );
    $invoice = $intent->metadata->invoice_id ?? 'N/A';
    $client  = $intent->metadata->client_email ?? 'Unknown';
    $subject = "💳 Payment received — \${$amount} · Invoice {$invoice}";
    $body    = "A payment of \${$amount} USD was received.\n\nInvoice: {$invoice}\nClient: {$client}\nStripe ID: {$intent->id}";
    wp_mail( $to, $subject, $body );
}

// ── URL params ────────────────────────────────────────────────────────────────
$invoice_id  = sanitize_text_field( $_GET['invoice']  ?? '' );
$amount_raw  = absint( $_GET['amount']  ?? 0 );   // in cents
$description = sanitize_text_field( $_GET['desc'] ?? 'Laws & Codes — Project Payment' );
$amount_fmt  = $amount_raw > 0 ? '$' . number_format( $amount_raw / 100, 2 ) : '';

get_header();
?>

<main class="lc-payment" id="main">

  <section class="payment-hero">
    <div class="payment-hero__inner">
      <div class="payment-hero__tag">
        <span class="payment-hero__dash"></span>
        Secure Payment
      </div>
      <h1 class="payment-hero__h1">
        <?php echo $invoice_id ? 'Invoice ' . esc_html( $invoice_id ) : 'Project Payment'; ?>
      </h1>
      <?php if ( $amount_fmt ) : ?>
        <div class="payment-hero__amount"><?php echo esc_html( $amount_fmt ); ?></div>
      <?php endif; ?>
      <p class="payment-hero__desc"><?php echo esc_html( $description ); ?></p>
    </div>
  </section>

  <section class="payment-body section">
    <div class="payment-body__grid">

      <!-- LEFT: Summary -->
      <div class="payment-summary">
        <div class="payment-summary__label">Payment summary</div>
        <div class="payment-summary__rows">
          <?php if ( $invoice_id ) : ?>
          <div class="payment-summary__row">
            <span>Invoice</span><span><?php echo esc_html( $invoice_id ); ?></span>
          </div>
          <?php endif; ?>
          <div class="payment-summary__row">
            <span>Description</span><span><?php echo esc_html( $description ); ?></span>
          </div>
          <?php if ( $amount_fmt ) : ?>
          <div class="payment-summary__row payment-summary__row--total">
            <span>Amount due</span><span><?php echo esc_html( $amount_fmt ); ?></span>
          </div>
          <?php endif; ?>
        </div>

        <div class="payment-summary__trust">
          <div class="payment-trust-item">
            <svg class="payment-trust-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>256-bit SSL encryption</span>
          </div>
          <div class="payment-trust-item">
            <svg class="payment-trust-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span>Credit card, debit &amp; ACH</span>
          </div>
          <div class="payment-trust-item">
            <svg class="payment-trust-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>PCI DSS compliant via Stripe</span>
          </div>
        </div>

        <?php
        $terms_note = get_field('payment_terms_note');
        if ( $terms_note ) : ?>
          <p class="payment-summary__terms"><?php echo esc_html( $terms_note ); ?></p>
        <?php else : ?>
          <p class="payment-summary__terms">Payments are processed securely by Stripe. Your card details are never stored on our servers. 50% deposit required to begin; remaining 50% due upon completion.</p>
        <?php endif; ?>
      </div>

      <!-- RIGHT: Stripe Payment Form -->
      <div class="payment-form-wrap">
        <div class="payment-form-wrap__label">Pay securely</div>

        <!-- Custom email field (before Stripe Elements mount) -->
        <div class="payment-field" id="email-field">
          <label class="payment-field__label" for="payer-email">Your email (for receipt)</label>
          <input
            class="payment-field__input"
            type="email"
            id="payer-email"
            name="payer-email"
            placeholder="you@yourbusiness.com"
            autocomplete="email"
          >
        </div>

        <!-- Stripe Elements mount point -->
        <div id="payment-element"></div>

        <!-- Error message -->
        <div id="payment-error" class="payment-error" role="alert" hidden></div>

        <!-- Submit -->
        <button id="submit-payment" class="payment-submit" disabled>
          <span id="submit-label">
            <?php echo $amount_fmt ? 'Pay ' . esc_html( $amount_fmt ) : 'Pay now'; ?>
          </span>
          <span id="submit-spinner" class="payment-spinner" hidden>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" stroke-opacity=".25"/>
              <path d="M12 2a10 10 0 0 1 10 10"/>
            </svg>
          </span>
        </button>

        <!-- Success state -->
        <div id="payment-success" class="payment-success" hidden>
          <div class="payment-success__icon">✓</div>
          <h3>Payment received!</h3>
          <p>Thank you — a receipt has been sent to your email. We'll be in touch shortly.</p>
          <a href="<?php echo esc_url( home_url('/') ); ?>" class="payment-success__home">Back to home</a>
        </div>

        <!-- Stripe badge -->
        <?php if ( get_field('stripe_logo_visible') !== false ) : ?>
        <div class="payment-stripe-badge">
          <svg viewBox="0 0 60 25" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Powered by Stripe" role="img">
            <path d="M5 9.7C5 7.7 6.7 7 8.6 7c2.6 0 4.2 1.4 4.2 1.4l-1.2 1.8S10.2 9 8.7 9c-.9 0-1.4.3-1.4.9 0 1.6 5.2 1 5.2 4.7C12.5 16.7 10.6 18 8.4 18 5.5 18 4 16.3 4 16.3l1.2-1.8S6.7 16 8.4 16c1 0 1.7-.4 1.7-1.1C10.1 13 5 13.4 5 9.7z" fill="#6772e5"/>
            <path d="M18.2 6h-2.4v3h-1.4v2.1h1.4v4.1c0 2 1.2 2.8 3 2.8.8 0 1.6-.2 1.6-.2v-2s-.5.1-.9.1c-.8 0-1.1-.3-1.1-1.1v-3.7h2.1V9h-2.1V6z" fill="#6772e5"/>
            <path d="M25.2 8.8c-2.8 0-4.7 2-4.7 4.6 0 2.9 1.9 4.6 4.8 4.6 1.4 0 2.7-.5 3.5-1.4l-1.4-1.5c-.5.5-1.2.8-2 .8-1.2 0-2.1-.7-2.3-1.8h6.1v-.7c0-2.8-1.7-4.6-4-4.6zm-2 3.8c.2-1 .9-1.7 2-1.7 1 0 1.8.7 1.9 1.7h-3.9z" fill="#6772e5"/>
            <path d="M35 8.8c-1 0-1.9.5-2.4 1.3V9h-2.2v8.8h2.4v-4.6c0-1.3.8-2.1 1.9-2.1.3 0 .7.1.7.1V9s-.2-.2-.4-.2z" fill="#6772e5"/>
            <path d="M37 6.2c0 .8.6 1.4 1.4 1.4.8 0 1.4-.6 1.4-1.4 0-.8-.6-1.4-1.4-1.4C37.6 4.8 37 5.4 37 6.2zM37.2 9v8.8h2.4V9h-2.4z" fill="#6772e5"/>
            <path d="M44.8 8.8c-2.8 0-4.7 2-4.7 4.6 0 2.9 1.9 4.6 4.8 4.6 1.4 0 2.7-.5 3.5-1.4l-1.4-1.5c-.5.5-1.2.8-2 .8-1.2 0-2.1-.7-2.3-1.8H49v-.7c0-2.8-1.7-4.6-4.2-4.6zm-2 3.8c.2-1 .9-1.7 2-1.7 1 0 1.8.7 1.9 1.7h-3.9z" fill="#6772e5"/>
          </svg>
          <span>Powered by Stripe</span>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </section>

</main>

<!-- Stripe.js v3 -->
<script src="https://js.stripe.com/v3/"></script>

<script>
(function() {
  const PUBLISHABLE_KEY = '<?php echo esc_js( defined('STRIPE_PUBLISHABLE_KEY') ? STRIPE_PUBLISHABLE_KEY : '' ); ?>';
  const AJAX_URL        = '<?php echo esc_js( admin_url('admin-ajax.php') ); ?>';
  const NONCE           = '<?php echo esc_js( wp_create_nonce('lc_stripe_nonce') ); ?>';
  const AMOUNT          = <?php echo intval( $amount_raw ); ?>;
  const INVOICE_ID      = '<?php echo esc_js( $invoice_id ); ?>';
  const DESCRIPTION     = '<?php echo esc_js( $description ); ?>';
  const RETURN_URL      = '<?php echo esc_js( get_permalink() . '?paid=1&invoice=' . urlencode( $invoice_id ) ); ?>';

  if ( ! PUBLISHABLE_KEY ) {
    document.getElementById('payment-error').textContent = 'Payment is not yet configured. Please contact hello@lawscodes.com.';
    document.getElementById('payment-error').hidden = false;
    return;
  }

  // If returning from 3DS redirect
  const urlParams = new URLSearchParams(window.location.search);
  if ( urlParams.get('paid') === '1' ) {
    showSuccess();
    return;
  }

  const stripe   = Stripe(PUBLISHABLE_KEY);
  let elements, paymentElement;
  let clientSecret = null;

  const submitBtn   = document.getElementById('submit-payment');
  const submitLabel = document.getElementById('submit-label');
  const spinner     = document.getElementById('submit-spinner');
  const errorDiv    = document.getElementById('payment-error');

  // Create PaymentIntent via WP AJAX then mount Elements
  async function init() {
    try {
      const res = await fetch(AJAX_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action:      'lc_create_payment_intent',
          nonce:       NONCE,
          amount:      AMOUNT,
          description: DESCRIPTION,
          invoice_id:  INVOICE_ID,
          email:       document.getElementById('payer-email').value,
        }),
      });
      const data = await res.json();
      if ( ! data.success ) throw new Error(data.data || 'Could not initialize payment.');

      clientSecret = data.data.clientSecret;

      elements = stripe.elements({
        clientSecret,
        appearance: {
          theme: 'flat',
          variables: {
            colorPrimary:         '#070945',
            colorBackground:      '#ffffff',
            colorText:            '#0d0d1a',
            colorDanger:          '#df1b41',
            fontFamily:           'Inter, system-ui, sans-serif',
            spacingUnit:          '4px',
            borderRadius:         '5px',
            colorTextPlaceholder: '#6b6b7e',
          },
          rules: {
            '.Input': {
              border:     '1px solid rgba(7,9,69,0.12)',
              boxShadow:  'none',
              padding:    '12px 14px',
              fontSize:   '14px',
            },
            '.Input:focus': {
              border:    '1.5px solid #070945',
              outline:   'none',
              boxShadow: '0 0 0 3px rgba(7,9,69,0.08)',
            },
            '.Label': {
              fontSize:      '12px',
              fontWeight:    '400',
              letterSpacing: '.02em',
              marginBottom:  '6px',
              color:         '#6b6b7e',
            },
          },
        },
      });

      paymentElement = elements.create('payment');
      paymentElement.mount('#payment-element');
      paymentElement.on('ready', () => { submitBtn.disabled = false; });

    } catch (err) {
      showError(err.message);
    }
  }

  // Lazily init when user fills email, or immediately if amount is pre-set
  if ( AMOUNT > 0 ) {
    init();
  } else {
    showError('No payment amount specified. Please use the link provided in your invoice.');
  }

  // Submit
  submitBtn.addEventListener('click', async () => {
    if ( ! clientSecret ) return;
    setLoading(true);
    errorDiv.hidden = true;

    const { error } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        return_url:           RETURN_URL,
        receipt_email:        document.getElementById('payer-email').value,
      },
    });

    // confirmPayment only reaches here on error (redirect otherwise)
    setLoading(false);
    showError(error.message);
  });

  function setLoading(loading) {
    submitBtn.disabled = loading;
    submitLabel.hidden = loading;
    spinner.hidden     = ! loading;
  }

  function showError(msg) {
    errorDiv.textContent = msg;
    errorDiv.hidden      = false;
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function showSuccess() {
    document.getElementById('payment-success').hidden = false;
    document.getElementById('email-field').hidden     = true;
    document.getElementById('payment-element').hidden = true;
    document.getElementById('submit-payment').hidden  = true;
  }
})();
</script>

<?php get_footer(); ?>
