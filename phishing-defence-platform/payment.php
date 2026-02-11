<?php
// === Global security and utility includes ===
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

// === Send security headers + generate CSP nonce ===
send_security_headers();
$nonce = csp_nonce();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Choose a plan – Phishing Defence</title>

<!-- Load global dashboard styling -->
<link rel="stylesheet" href="../css/dashboard.css">

<style>
.plan-box{border:2px solid #e11d48;border-radius:14px;padding:1.8rem;width:300px;
          display:flex;flex-direction:column;justify-content:space-between}
.plan-box h3{margin:0 0 .4rem 0;color:#16f2b3;font-size:1.4rem;text-align:center}
.price{font-size:1.8rem;font-weight:bold;margin:.2rem 0;text-align:center}
ul.benefits{padding-left:18px;margin:.6rem 0 1.2rem 0;font-size:.9rem}
.plan-btn{background:#e11d48;color:#fff;border:0;border-radius:6px;padding:10px;
          font-size:1rem;cursor:pointer;text-align:center;text-decoration:none}
.plans-grid{display:flex;gap:2rem;flex-wrap:wrap;justify-content:center}
h1{margin-bottom:.4rem}
</style>
</head>
<body>

<?php
// Include the sidebar and top navigation bar for consistency across pages
include 'sidebar-and-topbar.inc.php';
?>

<main class="dashboard-content">
  <h1>Choose your plan</h1>
  <p>Select the licence that fits you best.</p>

  <div class="plans-grid">

    <!-- PERSONAL PLAN -->
    <div class="plan-box">
      <div>
        <h3>Personal</h3>
        <div class="price">£19 / year</div>
        <ul class="benefits">
          <li>Access to all three modules</li>
          <li>Unlimited retries</li>
          <li>Email certificate on completion</li>
        </ul>
      </div>
      <!-- Redirects to checkout with plan=personal -->
      <a class="plan-btn" href="payment-checkout.php?plan=personal">Buy Personal</a>
    </div>

    <!-- BUSINESS PLAN -->
    <div class="plan-box">
      <div>
        <h3>Business</h3>
        <div class="price">£99 / year</div>
        <ul class="benefits">
          <li>Everything in Personal +</li>
          <li>10 user seats</li>
          <li>Admin performance dashboard</li>
          <li>Priority e-mail support</li>
        </ul>
      </div>
      <!-- Redirects to checkout with plan=business -->
      <a class="plan-btn" href="payment-checkout.php?plan=business">Buy Business</a>
    </div>

  </div>
</main>
</body>
</html>