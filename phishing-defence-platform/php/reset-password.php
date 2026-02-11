<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';   // sube 1 nivel

require_once LIB_DIR . '/db.php';
require_once LIB_DIR . '/security.php';
require_once LIB_DIR . '/csrf.php';
require_once LIB_DIR . '/auth.php'; // si no lo tienes, basta con password_hash()

if (function_exists('send_security_headers')) send_security_headers();

// Solo POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  redirect('reset-password.php?error=1');
}

// CSRF
$csrf = $_POST['csrf_token'] ?? null;
if (!csrf_validate($csrf)) {
  redirect('reset-password.php?error=1');
}

// Token
$raw = trim((string)($_POST['t'] ?? ''));
if ($raw === '') {
  redirect('reset-password.php?error=1');
}
$hash = hash('sha256', $raw);

// Passwords
$pass1 = (string)($_POST['password']  ?? '');
$pass2 = (string)($_POST['password2'] ?? '');
if ($pass1 === '' || $pass1 !== $pass2 || strlen($pass1) < 8) {
  redirect('reset-password.php?error=1&t=' . urlencode($raw));
}

// Buscar token válido
$row = null;
try {
  $res = db_query(
    'SELECT id, user_id, expires_at, used_at FROM password_resets WHERE token_hash = ? LIMIT 1',
    [$hash]
  );
  if ($res && is_array($res) && count($res) === 1) $row = $res[0];
} catch (Throwable $e) {
  redirect('reset-password.php?error=1');
}

if (!$row || !empty($row['used_at']) || strtotime((string)$row['expires_at']) < time()) {
  redirect('reset-password.php?error=1');
}

$userId  = (int)$row['user_id'];
$newHash = password_hash($pass1, PASSWORD_DEFAULT);

try {
  db_query('UPDATE users SET password_hash = ? WHERE id = ? LIMIT 1', [$newHash, $userId]);
  db_query('UPDATE password_resets SET used_at = NOW() WHERE id = ? LIMIT 1', [$row['id']]);
  db_query('DELETE FROM password_resets WHERE user_id = ? AND id <> ?', [$userId, $row['id']]);
} catch (Throwable $e) {
  redirect('reset-password.php?error=1');
}

redirect('reset-password.php?ok=1');
