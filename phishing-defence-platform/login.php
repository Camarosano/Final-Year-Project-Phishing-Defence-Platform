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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Phishing Defence</title>
  <link rel="stylesheet" href="../css/signup.css" />
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
</head>
<body>
  <!-- Top logo header -->
  <header>
    <div class="logo">PHISHING DEFENCE</div>
  </header>
  <!-- Login form container -->
  <div class="form-container">
    <h1>Agent Login</h1>
    <p class="start-text">Enter your credentials to continue...</p>

    <!-- Error message displayed if login fails (triggered via GET parameter) -->
    <?php if (isset($_GET['error'])): ?>
      <div class="login-error-banner">
        Invalid email or password. Please try again.
      </div>
    <?php endif; ?>

    <!-- Login form DIrectly pointing to the correct PHP processor and including CSRF field -->
    <form action="php/login.php" method="POST">
      <?= csrf_field() ?>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required />

      <button type="submit">Login</button>
    </form>
    <!-- Redirect to signup page if user doesn't have an account -->
    <p class="form-note">
      Don't have an account? <a href="signup.html">Create one here</a>.
    </p>
    <p class="form-note">
  <a href="<?= app_url('forgot-password.php') ?>">Forgot your password?</a>
</p>

  </div>
  <!-- Footer with copyright notice -->
  <footer>
    <p>&copy; 2025 Phishing Defence Platform</p>
  </footer>

</body>
</html>