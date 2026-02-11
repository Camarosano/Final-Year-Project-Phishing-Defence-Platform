<?php
declare(strict_types=1);

// Ruta correcta al config (no subas de más)
require_once __DIR__ . '/config/config.php';

// Solo librerías necesarias para la vista
require_once LIB_DIR . '/security.php';
require_once LIB_DIR . '/csrf.php';

if (function_exists('send_security_headers')) send_security_headers();

$csrf = function_exists('csrf_token') ? csrf_token() : '';
$t    = isset($_GET['t']) ? (string)$_GET['t'] : '';
$err  = isset($_GET['error']);
$ok   = isset($_GET['ok']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reset Password</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../css/signup.css">
</head>
<body>
  <header><div class="logo">PHISHING DEFENCE</div></header>

  <div class="form-container">
    <h1>Set a new password</h1>

    <?php if ($ok): ?>
      <div class="login-error-banner" style="background:#DCFCE7;color:#166534;border:1px solid #16a34a">
        Your password has been updated. You can now <a href="<?= app_url('login.php') ?>">sign in</a>.
      </div>
    <?php else: ?>
      <?php if ($err): ?>
        <div class="login-error-banner">Invalid or expired link. Request a new one.</div>
      <?php endif; ?>

      <form action="<?= url('php/reset-password.php') ?>" method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
        <input type="hidden" name="t" value="<?= htmlspecialchars($t, ENT_QUOTES) ?>">

        <label>New password</label>
        <input type="password" name="password" required minlength="8" autocomplete="new-password">

        <label>Confirm password</label>
        <input type="password" name="password2" required minlength="8" autocomplete="new-password">

        <button type="submit">Update password</button>
      </form>

      <p class="form-note"><a href="<?= app_url('forgot-password.php') ?>">Request a new link</a></p>
    <?php endif; ?>
  </div>
</body>
</html>
