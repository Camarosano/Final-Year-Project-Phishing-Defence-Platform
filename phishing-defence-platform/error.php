<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once LIB_DIR . '/security.php';

send_security_headers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Something went wrong</title>

  <!-- Si tus CSS están en /Website-proyect/css -->
  <link rel="stylesheet" href="<?= app_url('../css/signup.css') ?>" />
  <style>
    .error-message { margin-top: 8px; line-height: 1.5; }
    .actions { margin-top: 18px; display: flex; gap: 12px; flex-wrap: wrap; }
    .btn-link {
      display: inline-block; text-decoration: none; padding: 10px 14px; border-radius: 8px;
      background: #e11d48; color: #fff; font-weight: 600;
    }
    .btn-link:focus-visible { outline: 2px solid #22c55e; outline-offset: 2px; }
  </style>
</head>
<body>
  <header><div class="logo">PHISHING DEFENCE</div></header>

  <main class="form-container" role="main">
    <h1>
      Error encountered<br />
      <small>There was an issue processing your request.</small>
    </h1>

    <p class="error-message">
      Please try again. If the issue persists, return to the login or signup page.
    </p>

    <div class="actions" aria-label="Available actions">
      <a class="btn-link" href="<?= app_url('signup.php') ?>">Try signup again</a>
      <a class="btn-link" href="<?= app_url('login.php') ?>">Go to login</a>
    </div>
  </main>

  <footer><p>&copy; 2025 Phishing Defence Platform</p></footer>
</body>
</html>
