<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

// Cargar libs desde la raíz usando LIB_DIR
require_once LIB_DIR . '/db.php';
require_once LIB_DIR . '/security.php';
require_once LIB_DIR . '/csrf.php';

if (function_exists('send_security_headers')) send_security_headers();

// Solo POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  redirect('forgot-password.php?sent=1');
}

// CSRF
$token = $_POST['csrf_token'] ?? null;
if (!csrf_validate($token)) {
  redirect('forgot-password.php?sent=1');
}

// Rate limit simple
$_SESSION['pw_reset_try']  = $_SESSION['pw_reset_try']  ?? 0;
$_SESSION['pw_reset_last'] = $_SESSION['pw_reset_last'] ?? 0;
if (time() - (int)$_SESSION['pw_reset_last'] < 20 && (int)$_SESSION['pw_reset_try'] >= 3) {
  redirect('forgot-password.php?sent=1');
}
$_SESSION['pw_reset_try']++;
$_SESSION['pw_reset_last'] = time();

$emailRaw = trim((string)($_POST['email'] ?? ''));
if ($emailRaw === '' || !filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
  redirect('forgot-password.php?sent=1');
}
$email = mb_strtolower($emailRaw);

// Buscar usuario (no revelar existencia)
$user = null;
try {
  $rows = db_query('SELECT id, email FROM users WHERE email = ? LIMIT 1', [$email]);
  if ($rows && is_array($rows) && count($rows) === 1) $user = $rows[0];
} catch (Throwable $e) {
  redirect('forgot-password.php?sent=1');
}

if ($user) {
  try { db_query('DELETE FROM password_resets WHERE user_id = ?', [$user['id']]); } catch (Throwable $e) {}

  $rawToken  = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
  $tokenHash = hash('sha256', $rawToken);
  $expiresAt = date('Y-m-d H:i:s', time() + 30 * 60);
  $ip        = $_SERVER['REMOTE_ADDR'] ?? null;

  try {
    db_query(
      'INSERT INTO password_resets (user_id, token_hash, expires_at, request_ip) VALUES (?,?,?,?)',
      [$user['id'], $tokenHash, $expiresAt, $ip]
    );
  } catch (Throwable $e) {}

  // Log cómodo en Windows dentro del proyecto
  $logFile  = dirname(__DIR__) . '/tmp/phishing_reset_links.log';
  if (!is_dir(dirname($logFile))) @mkdir(dirname($logFile), 0777, true);
  $resetLink = url('reset-password.php') . '?t=' . urlencode($rawToken);
  @file_put_contents($logFile, sprintf("[%s] %s -> %s\n", date('c'), $email, $resetLink), FILE_APPEND);
}

redirect('forgot-password.php?sent=1');
