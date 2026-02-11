<?php
declare(strict_types=1);

// Includes
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

// Security headers and CSP nonce
send_security_headers();
$nonce = csp_nonce();

// Read selected plan
$plan  = $_GET['plan'] ?? '';

// Valid plans
$plans = [
    'personal' => 19,
    'business' => 99,
];

// Validate plan and redirect if invalid
if (!array_key_exists($plan, $plans)) {
    header('Location: payment.php');
    exit;
}

// Store selection for the success screen
$_SESSION['pending_plan']  = $plan;
$_SESSION['pending_price'] = $plans[$plan];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout – <?= ucfirst($plan) ?> plan</title>

<!-- Shared stylesheet -->
<link rel="stylesheet" href="../css/dashboard.css">

<!-- Page-specific styles -->
<style>
header.top-bar{
    background:transparent;border:none;height:48px;padding:0 10px;
    display:flex;justify-content:flex-end;align-items:center;
}
header.top-bar .logo-small{display:none}
button.menu-toggle{
    width:44px;height:44px;background:#e11d48;border:2px solid #e11d48;
    border-radius:6px;color:#fff;font-size:22px;line-height:1;cursor:pointer;
}
main{
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:calc(100vh - 56px);
    padding-top:56px;
}
form{
    max-width: 420px;
    margin: 2.5rem auto;
    background: #111;
    padding: 2rem;
    border-radius: 14px;
    border: 2px solid #e11d48;
    color:#fff;
    font-family: Arial,Helvetica,sans-serif;
}
h2{margin:0 0 1rem;font-weight:600;font-size:1.4rem}
label{display:block;margin:.85rem 0 .35rem;font-size:.92rem}
input{
    width:100%;padding:.65rem;border-radius:6px;
    border:1px solid #555;background:#181818;color:#fff;font-size:.95rem
}
.row{display:flex;gap:1rem}
button{
    margin-top:1.6rem;background:#e11d48;color:#fff;border:0;
    padding:13px 24px;font-size:1rem;border-radius:6px;cursor:pointer;
    width:100%
}
small{display:block;font-size:.8rem;opacity:.7;margin-top:.8rem}
</style>
</head>
<body>

<?php include 'sidebar-and-topbar.inc.php'; ?><!-- Layout: sidebar + top bar -->

<main>
  <!-- Checkout form (demo) -->
  <form method="post" action="payment-success.php" autocomplete="off">
    <h2><?= ucfirst($plan) ?> licence &nbsp; · &nbsp; £<?= $plans[$plan] ?></h2>

    <label>Cardholder name
      <input type="text" name="card_name" required>
    </label>

    <label>Card number
      <input id="cardNumber"
             type="text"
             name="card_number"
             inputmode="numeric"
             pattern="\d{16}"
             maxlength="16"
             placeholder="16-digit number"
             required>
    </label>

    <div class="row">
      <label style="flex:1">Expiry (MM/YY)
        <input id="exp"
               type="text"
               name="exp"
               inputmode="numeric"
               pattern="\d{2}/\d{2}"
               maxlength="5"
               placeholder="08/28"
               required>
      </label>

      <label style="flex:1">CVV
        <input id="cvv"
               type="text"
               name="cvv"
               inputmode="numeric"
               pattern="\d{3}"
               maxlength="3"
               placeholder="123"
               required>
      </label>
    </div>

    <button type="submit">Pay £<?= $plans[$plan] ?></button>
    
  </form>
</main>

<!-- Field formatting -->
<script>
// Card number
const cardNum = document.getElementById('cardNumber');
cardNum.addEventListener('input', e => {
  e.target.value = e.target.value.replace(/[^\d]/g, '').slice(0, 16);
});

// Expiry (MM/YY)
const exp = document.getElementById('exp');
exp.addEventListener('input', e => {
  let v = e.target.value.replace(/[^\d]/g, '').slice(0, 4);
  if (v.length > 2) v = v.slice(0, 2) + '/' + v.slice(2);
  e.target.value = v;
});

// CVV
const cvv = document.getElementById('cvv');
cvv.addEventListener('input', e => {
  e.target.value = e.target.value.replace(/[^\d]/g, '').slice(0, 3);
});
</script>
</body>
</html>