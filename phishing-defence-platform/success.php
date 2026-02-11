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
  <title>Registration Successful</title>

  <!-- Si tus CSS están en /Website-proyect/css -->
  <link rel="stylesheet" href="<?= app_url('../css/signup.css') ?>" />
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet" />

  <style>
    .success-message { margin-top: 8px; line-height: 1.6; }

    /* centramos el contenedor del botón */
    .actions{
      margin-top: 22px;
      display: flex;
      justify-content: center; /* ← centrado */
    }

    /* botón un poco más grande + animación suave al hover */
    .btn-link{
      display: inline-block;
      text-decoration: none;
      padding: 12px 22px;               /* ← un poco más grande */
      border-radius: 10px;
      background: #22c55e;
      color: #0b0b0b;
      font-weight: 700;
      font-size: 1rem;
      transition: transform .12s ease, filter .12s ease, box-shadow .12s ease;
    }
    .btn-link:hover{
      transform: scale(2.5);           /* ← ligero aumento al hover */
      filter: brightness(1.08);
      box-shadow: 0 0 0 2px #22c55e inset;
    }
    .btn-link:focus-visible{
      outline: 2px solid #e11d48;
      outline-offset: 2px;
    }
  </style>
</head>
<body>
  <!-- Fondo Matrix -->
  <canvas id="Matrix"></canvas>

  <header><div class="logo">PHISHING DEFENCE</div></header>

  <main class="form-container" role="main" aria-labelledby="success-title">
    <h1 id="success-title" class="alert">Registration Successful</h1>
    <p class="success-message">
      Welcome, agent.<br />
      Your training begins now.<br />
      Prepare to defend against online threats.
    </p>

    <div class="actions" aria-label="Available actions">
      <a class="btn-link" href="<?= app_url('login.php') ?>">Proceed to login</a>
    </div>
  </main>

  <footer><p>&copy; 2025 Phishing Defence Platform</p></footer>

  <!-- Ajusta a Matrix.js o matrix.js según tu archivo real -->
  <script src="<?= app_url('../js/Matrix.js') ?>" defer></script>
</body>
</html>