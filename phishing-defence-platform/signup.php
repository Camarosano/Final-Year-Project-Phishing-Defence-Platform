<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once LIB_DIR . '/security.php';
require_once LIB_DIR . '/csrf.php';

send_security_headers();
$nonce = csp_nonce();
$csrf  = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up - Phishing Defence</title>
  <!-- si tu CSS está en /Website-proyect/css usa ../css/... -->
  <link rel="stylesheet" href="<?= app_url('../css/signup.css') ?>" />
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
</head>
<body>
  <header><div class="logo">PHISHING DEFENCE</div></header>

  <div class="form-container">
    <h1>Create Your Account</h1>

    <form action="<?= url('php/signup.php') ?>" method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

      <label for="username">Username</label>
      <input type="text" id="username" name="username" required minlength="3" maxlength="50"/>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required minlength="8" />

      <button type="submit">Sign Up</button>
    </form>

    <p class="form-note">
      Already have an account? <a href="<?= app_url('login.php') ?>">Log in here</a>.
    </p>
    <p class="form-note">Your data will be encrypted and securely stored. Don’t use a real password.</p>
  </div>

  <footer><p>&copy; 2025 Phishing Defence Platform</p></footer>
</body>
</html>
