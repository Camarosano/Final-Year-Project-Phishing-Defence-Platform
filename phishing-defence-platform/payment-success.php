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

// Simulated payment "processing" – in real applications this would involve a payment gateway
$plan  = $_SESSION['pending_plan']  ?? null;
$price = $_SESSION['pending_price'] ?? null;

// If the payment session variables are missing, redirect to pricing page
if (!$plan || !$price) {
    header('Location: payment.php');
    exit;
}

// Mark the purchase as completed by storing the licence in session
$_SESSION['licence'] = $plan;

// Remove temporary payment session data
unset($_SESSION['pending_plan'], $_SESSION['pending_price']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment approved</title>

<!-- Load shared dashboard CSS -->
<link rel="stylesheet" href="../css/dashboard.css">

<style>
body {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #0f0f0f;
  color: #fff;
  font-family: Arial;
}

.card {
  border: 2px solid #16a34a;
  border-radius: 14px;
  padding: 2.4rem;
  text-align: center;
  max-width: 400px;
}

h1 {
  color: #16a34a;
  margin: 0 0 .6rem 0;
}

a.btn {
  display: inline-block;
  margin-top: 1.4rem;
  background: #e11d48;
  color: #fff;
  padding: 10px 22px;
  border-radius: 6px;
  text-decoration: none;
}
</style>
</head>
<body>

<?php
// Load the sidebar and topbar (optional in this case)
include 'sidebar-and-topbar.inc.php';
?>

<!-- Confirmation card shown after successful purchase -->
<div class="card">
    <h1>Purchase accepted!</h1>
    <p>
      Thank you for choosing the <b><?= ucfirst($plan) ?></b> plan.<br>
      Your journey down the red-pill path has just begun.
    </p>
    <p>Enjoy unlimited access to all available courses.</p>

    <!-- Return to dashboard button -->
    <a class="btn" href="dashboard.php">Return to dashboard</a>
</div>
</body>
</html>