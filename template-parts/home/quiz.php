<?php /* template-parts/home/quiz.php */ defined('ABSPATH')||exit; ?>
<section class="quiz-section" id="lc-quiz-section">
  <div class="section-kicker">Find your fit</div>
  <h2 class="section-title">Not sure where to start?</h2>
  <p class="section-sub">Answer 4 quick questions and we'll point you to the right service.</p>

  <div class="quiz-layout" style="margin-top:40px">
    <div class="quiz-card" id="lc-quiz">
      <div class="quiz-progress"><div class="quiz-progress-fill" style="width:0%"></div></div>
      <div class="quiz-body-wrap">
        <div class="quiz-body">
          <div class="quiz-step">Question 1 of 4</div>
          <div class="quiz-question">Loading...</div>
          <div class="quiz-options"></div>
        </div>
      </div>
      <div class="quiz-footer">
        <div class="quiz-dots">
          <div class="quiz-dot active"></div>
          <div class="quiz-dot"></div>
          <div class="quiz-dot"></div>
          <div class="quiz-dot"></div>
        </div>
        <div style="display:flex;gap:8px">
          <button class="quiz-btn-back js-quiz-back" style="display:none">← Back</button>
          <button class="quiz-btn js-quiz-next" disabled>Next →</button>
        </div>
      </div>
      <div class="quiz-result" id="lc-quiz-result">
        <div class="result-badge"><div class="result-badge-dot"></div>Our recommendation</div>
        <div class="result-service">—</div>
        <p class="result-desc"></p>
        <div class="result-includes"><div class="result-includes-title">What's included</div><div class="js-result-includes"></div></div>
        <div class="result-price"></div>
        <a href="#" class="result-cta js-result-cta">Get a quote for this →</a>
        <button class="result-retry js-quiz-retry">Retake the quiz</button>
      </div>
    </div>

    <div class="quiz-sidebar">
      <div class="quiz-sidebar-card">
        <h3 class="section-title" style="font-size:16px;margin-bottom:6px">All services</h3>
        <p class="section-sub" style="font-size:12px;margin-bottom:14px">Not sure what the quiz will tell you?</p>
        <?php
        $services = [
          ['Custom website design',   'From $1,500'   ],
          ['E-commerce development',  'From $2,500'   ],
          ['SEO &amp; maintenance',   'From $300/mo'  ],
          ['Branding &amp; logo',     'From $600'     ],
        ];
        foreach ( $services as [ $n, $p ] ) : ?>
          <div class="sidebar-service-row">
            <span class="sidebar-service-name"><?php echo $n; ?></span>
            <span class="sidebar-service-price"><?php echo $p; ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="quiz-note-card">
        <div class="quiz-note-title">Every project starts with a call.</div>
        <p class="quiz-note-sub">No pressure — just a real conversation about your business and where you want to take it.</p>
        <div class="quiz-note-slots">◆ 2 project slots open this quarter</div>
      </div>
    </div>
  </div>
</section>
