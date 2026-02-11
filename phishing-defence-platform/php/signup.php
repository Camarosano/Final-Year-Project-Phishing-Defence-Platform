<?php
declare(strict_types=1);

/**
 * /phishing-defence-platform/php/signup.php
 */

require_once __DIR__ . '/../config/config.php';
require_once LIB_DIR . '/db.php';
require_once LIB_DIR . '/security.php';
require_once LIB_DIR . '/csrf.php';

send_security_headers();

// Destinos dentro de phishing-defence-platform
$SUCCESS_PAGE = 'success.php';
$ERROR_PAGE   = 'error.php';

// Solo POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  redirect($ERROR_PAGE);
}

// CSRF
$token = $_POST['csrf_token'] ?? null;
if (!csrf_validate($token)) {
  redirect($ERROR_PAGE);
}

// Inputs
$usernameRaw = trim((string)($_POST['username'] ?? ''));
$emailRaw    = trim((string)($_POST['email'] ?? ''));
$passwordRaw = (string)($_POST['password'] ?? '');

// Validaciones básicas
if ($usernameRaw === '' || $emailRaw === '' || $passwordRaw === '') redirect($ERROR_PAGE);
if (!filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) redirect($ERROR_PAGE);
if (mb_strlen($usernameRaw) < 3 || mb_strlen($usernameRaw) > 50) redirect($ERROR_PAGE);
if (mb_strlen($passwordRaw) < 8) redirect($ERROR_PAGE);

// Normalización
$email    = function_exists('mb_strtolower') ? mb_strtolower($emailRaw, 'UTF-8') : strtolower($emailRaw);
$username = $usernameRaw;

// Unicidad
try {
  $existing = db_query('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1', [$email, $username]);
} catch (Throwable $e) {
  redirect($ERROR_PAGE);
}
if ($existing && count($existing) > 0) redirect($ERROR_PAGE);

// Hash
$hash = password_hash_secure($passwordRaw);

// Insert
try {
  db_exec('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)', [$username, $email, $hash]);
} catch (Throwable $e) {
  redirect($ERROR_PAGE);
}

// OK
redirect($SUCCESS_PAGE);
