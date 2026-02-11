<?php
declare(strict_types=1);

// Includes: configuration and security helpers
require_once __DIR__ . '/config/config.php';

/** try /Website-proyect/lib and /phishing-defence-platform/lib */
function require_lib(string $file): void {
  $candidates = [
    dirname(__DIR__) . '/lib/' . $file,
    __DIR__ . '/lib/' . $file,
  ];
  foreach ($candidates as $p) {
    if (is_file($p)) { require_once $p; return; }
  }
  http_response_code(500);
  echo "Bootstrap error: no se encuentra lib/{$file}.<br>Buscado en:<br>"
     . htmlspecialchars($candidates[0]) . "<br>"
     . htmlspecialchars($candidates[1]);
  exit;
}

require_lib('security.php');
require_lib('csrf.php');

// Security headers and CSRF token
if (function_exists('send_security_headers')) send_security_headers();
$csrf = function_exists('csrf_token') ? csrf_token() : '';
$sent = isset($_GET['sent']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Forgot Password</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../css/signup.css">
  <style>
    html, body { height: 100%; }
    /* centre the box perfectly in the viewport */
    .center-viewport {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 24px; /* small breathing room on tiny screens */
    }
  </style>
</head>
<body>
  <header><div class="logo">PHISHING DEFENCE</div></header>

  <main class="center-viewport">
    <div class="form-container">
      <h1>Password recovery</h1>

      <?php if ($sent): ?>
        <div class="login-error-banner" style="background:#DCFCE7;color:#166534;border:1px solid #16a34a">
          If an account exists for that email, we’ve sent a reset link.
        </div>
      <?php else: ?>
        <p class="start-text">Enter your email and we'll send you a reset link.</p>
        <form action="<?= url('php/forgot-password.php') ?>" method="POST" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
          <label>Email</label>
          <input type="email" name="email" required autocomplete="email">
          <button type="submit">Send reset link</button>
        </form>
      <?php endif; ?>

      <p class="form-note"><a href="<?= app_url('login.php') ?>">Back to login</a></p>
    </div>
  </main>
</body>
</html>
