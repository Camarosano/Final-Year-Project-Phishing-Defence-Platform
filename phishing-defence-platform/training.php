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
  <title>Phishing Defence - Training</title>
  <!-- Si tus CSS están en la raíz /Website-proyect/css -->
  <link rel="stylesheet" href="<?= app_url('../css/style.css') ?>" />
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
</head>
<body>
  <header>
    <div class="logo">PHISHING DEFENCE</div>
  </header>

  <main class="hero">
    <h1>The online world is full of hidden threats.</h1>
    <p class="description">From phishing emails to malicious websites — humans are the weakest link.</p>
    <p class="start-text">Every time you connect to the internet, you are exposing yourself to potential attacks. The most common entry point? <strong>You.</strong></p>

    <div class="button-container">
      <a href="<?= app_url('signup.php') ?>" class="cta-button red">
        Create your account to begin your defence
      </a>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 Phishing Defence Platform</p>
  </footer>
</body>
</html>
