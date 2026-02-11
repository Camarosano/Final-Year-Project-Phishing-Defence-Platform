<?php
// === Global security and utility includes ===
// These files contain configuration, database connection, security headers,
// CSRF protection, and authentication helpers.
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

// === Send security headers + generate CSP nonce ===
// This ensures every page has strict security headers and
// inline scripts can only run with the provided nonce.
send_security_headers();
$nonce = csp_nonce();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FAQ – Phishing Defence</title>

  <!-- Reuse the shared dashboard stylesheet for consistent styling -->
  <link rel="stylesheet" href="../css/dashboard.css">

  <style>
    /* --- Page-specific styling for FAQ layout --- */
    main {
      max-width: 900px;
      margin: 90px auto 60px;
      padding: 0 1rem;
      font-family: Arial, Helvetica, sans-serif;
    }

    h1, h2 {
      color: #16f195;
      font-family: 'Share Tech Mono', monospace;
      text-align: center;
    }

    details {
      background: #111;
      border: 1px solid #f00;
      border-radius: 6px;
      margin: 12px 0;
      padding: 0.6rem 1rem;
    }

    summary {
      cursor: pointer;
      font-weight: bold;
      color: #f33;
    }

    details[open] summary {
      color: #16f195;
    }

    details p {
      margin: 0.6rem 0 0;
      color: #ddd;
      line-height: 1.45;
    }
  </style>
</head>
<body>

<?php
// Reuse the sidebar and top bar layout from shared component
include 'sidebar-and-topbar.inc.php';
?>

<main>
  <h1>Frequently Asked Questions</h1>
  <h2>Your quick guide to Phishing Defence</h2>

  <!-- Each <details> block represents a collapsible question-answer pair -->

  <!-- Question 1 -->
  <details>
    <summary>1.&nbsp;How do I start the Basic module?</summary>
    <p>Log in → Dashboard → click the <strong>Basic</strong> card “Start / Retry”. The test launches immediately.</p>
  </details>

  <!-- Question 2 -->
  <details>
    <summary>2.&nbsp;How do I unlock the Intermediate module?</summary>
    <p>Score at least 60 % (6 / 10) in the Basic module. Once you pass, the Intermediate card on your Dashboard turns <b>green</b> and the lock disappears.</p>
  </details>

  <!-- Question 3 -->
  <details>
    <summary>3.&nbsp;What score do I need to pass each module?</summary>
    <p>• <b>Basic</b>: 60 % &nbsp;• <b>Intermediate</b>: 70 % &nbsp;• <b>Advanced</b>: 80 %</p>
  </details>

  <!-- Question 4 -->
  <details>
    <summary>4.&nbsp;Can I retry a module if I fail?</summary>
    <p>Absolutely. Use the <em>Retry test</em> button shown on the result screen, or simply click the module card on the Dashboard again.</p>
  </details>

  <!-- Question 5 -->
  <details>
    <summary>5.&nbsp;Do retries overwrite my previous score?</summary>
    <p>No. We keep every attempt in your personal history. The charts on the Dashboard plot <em>all</em> your scores chronologically.</p>
  </details>

  <!-- Question 6 -->
  <details>
    <summary>6.&nbsp;What do the coloured dots on the charts mean?</summary>
    <p>Green&nbsp;• = pass, Red&nbsp;• = fail. Hover (or tap) a dot to see your exact score for that attempt.</p>
  </details>

  <!-- Question 7 -->
  <details>
    <summary>7.&nbsp;How is my score calculated?</summary>
    <p>+1 point per correct answer. Checkbox questions penalise –0.25 per extra tick; ranking errors also subtract 0.25.</p>
  </details>

  <!-- Question 8 -->
  <details>
    <summary>8.&nbsp;I got 9 / 15 but still failed Intermediate. Why?</summary>
    <p>Intermediate needs 70 % → 10.5 points minimum. A 9/15 score is 60 %, which isn’t enough to pass.</p>
  </details>

  <!-- Question 9 -->
  <details>
    <summary>9.&nbsp;How do I see which answers I got wrong?</summary>
    <p>On the result page click “Review answers” (bottom-right). Correct choices are highlighted; your selections are shown in amber.</p>
  </details>

  <!-- Question 10 -->
  <details>
    <summary>10.&nbsp;Why do some questions include images?</summary>
    <p>Visual cues are crucial for phishing detection. Screenshots mimic real-world e-mails, chats or QR codes so you can practise spot-the-phish skills.</p>
  </details>

  <!-- Question 11 -->
  <details>
    <summary>11.&nbsp;Are the tests timed?</summary>
    <p>No. Take as long as you need—but avoid refreshing the page, otherwise your current answer will be lost.</p>
  </details>

  <!-- Question 12 -->
  <details>
    <summary>12.&nbsp;Why do you store my results locally?</summary>
    <p>We keep data in your session only to draw the progress charts. Nothing is sent outside the platform.</p>
  </details>

  <!-- Question 13 -->
  <details>
    <summary>13.&nbsp;When is my data deleted?</summary>
    <p>All attempt history is automatically purged on <strong>30 September <?= date('Y'); ?></strong>, or immediately if you click “Clear my data” in <em>Settings → Privacy</em>.</p>
  </details>

  <!-- Question 14 -->
  <details>
    <summary>14.&nbsp;Do I need to pay to retry?</summary>
    <p>No. Your subscription covers unlimited attempts on every module for the duration of your licence.</p>
  </details>

  <!-- Question 15 -->
  <details>
    <summary>15.&nbsp;What payment methods do you accept?</summary>
    <p>We currently support Visa, MasterCard, AMEX and corporate purchase orders. See the <a href="payment.php">Payment Method</a> page for step-by-step instructions.</p>
  </details>

  <!-- Question 16 -->
  <details>
    <summary>16.&nbsp;How secure is my payment information?</summary>
    <p>Payments are processed by Stripe. We never store card numbers on our servers—only a tokenised reference.</p>
  </details>

  <!-- Question 17 -->
  <details>
    <summary>17.&nbsp;Can I download a certificate?</summary>
    <p>Yes. After passing the Advanced module you’ll see a “Download Certificate” button on your result screen.</p>
  </details>

  <!-- Question 18 -->
  <details>
    <summary>18.&nbsp;My chart disappeared—what happened?</summary>
    <p>Charts use <code>sessionStorage</code>. If you cleared cookies or browsed in Incognito, the history resets. Your next attempt will start a fresh chart.</p>
  </details>

  <!-- Question 19 -->
  <details>
    <summary>19.&nbsp;Does HTTPS always mean a site is safe?</summary>
    <p>No. HTTPS only encrypts traffic—it doesn’t guarantee legitimacy. That’s why our quizzes include homograph or typo-domain questions.</p>
  </details>

  <!-- Question 20 -->
  <details>
    <summary>20.&nbsp;Where can I get further help?</summary>
    <p>Contact <a href="mailto:support@phish-defence.io">support@phish-defence.io</a> or open a ticket from the <em>Help</em> tab in your Dashboard sidebar.</p>
  </details>
</main>

</body>
</html>