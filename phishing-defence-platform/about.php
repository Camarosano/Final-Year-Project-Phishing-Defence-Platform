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
<title>About Us – Phishing Defence</title>
<link rel="stylesheet" href="../css/dashboard.css"><!-- tu hoja global -->

<style>
/* --- SOLO toques de maquetación propios de esta página --- */
main.about-wrap{
    max-width:920px;margin:0 auto;padding:96px 24px 48px; /* 96 = 56-px top-bar + 40 */
    color:#e5e7eb;font-family:Arial,Helvetica,sans-serif;line-height:1.55;
}
main h1{font-size:2.2rem;margin:0 0 1.2rem;color:#e11d48}
main h2{font-size:1.5rem;margin:2.4rem 0 .8rem;color:#22c55e}
.highlight{color:#22c55e;font-weight:600}
.plan-box{
    display:flex;gap:2rem;margin-top:1.8rem;flex-wrap:wrap;
}
.plan{
    flex:1 1 260px;border:2px solid #e11d48;border-radius:12px;padding:1.2rem;
    background:#111;min-width:250px;
}
.plan h3{margin:.2rem 0 .8rem;font-size:1.3rem;color:#e11d48}
.plan ul{padding-left:1.2rem;margin:0}
</style>
</head>
<body>
<?php include 'sidebar-and-topbar.inc.php'; ?>

<main class="about-wrap">
  <h1>Who we are</h1>
  <p>
    <span class="highlight">Phishing Defence</span> started with a simple idea:
    <em>make the internet safer for everyone</em> by turning cyber-security
    awareness into an engaging, game-like journey.  Whether you are a private
    individual curious about protecting your inbox, or a global enterprise
    looking to up-skill thousands of employees, our mission is identical —
    deliver hands-on, affordable training that actually sticks.
  </p>

  <h2>Our vision</h2>
  <p>
    We imagine a future where every person can recognise a malicious e-mail,
    text or call in seconds.  A world where entire organisations share a
    <strong>security-first mindset</strong>, not because they read a dusty
    policy once a year, but because they practised every week and had fun doing
    it.
  </p>

  <h2>Why “fun” matters</h2>
  <p>
    Research shows that <em>interactive micro-learning</em> boosts retention by
    up to 90 %.  So we combine progressive difficulty, instant feedback and a
    sprinkle of hacker lore to keep motivation high.  Users who complete our
    Advanced module finish with the skills and mentality of an
    <span class="highlight">ethical hacker user</span> — someone who can
    identify threats, report them responsibly and help friends &amp; colleagues
    do the same.
  </p>

  <h2>Pricing that scales</h2>
  <div class="plan-box">
      <div class="plan">
         <h3>Personal licence &ndash; £19 / year</h3>
         <ul>
           <li>All three training modules</li>
           <li>Unlimited re-takes and score tracking</li>
           <li>Monthly phishing challenge e-mail</li>
         </ul>
      </div>

      <div class="plan">
         <h3>Business licence &ndash; £99 / year</h3>
         <ul>
           <li>Everything in Personal, plus&hellip;</li>
           <li>Centralised dashboard for managers</li>
           <li>CSV export of employee progress</li>
           <li>Priority support <span class="highlight">24 / 7</span></li>
         </ul>
      </div>
  </div>

  <h2>Data &amp; privacy</h2>
  <p>
    Scores are saved <strong>locally</strong> in your browser session.  At the
    end of September all training data is wiped automatically, ensuring a fresh
    start for the next cohort and guaranteeing your privacy.
  </p>

  <h2>Join us!</h2>
  <p>
    Ready to level-up your security skills?  Head over to
    <a href="payment.php" style="color:#e11d48;text-decoration:underline">Payment&nbsp;Method</a>
    to unlock full access and let the red-pill journey begin.
  </p>
</main>

</body>
</html>
