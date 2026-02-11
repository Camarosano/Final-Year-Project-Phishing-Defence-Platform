<?php
// Escapado seguro
function e(?string $s): string {
  return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Nonce para CSP
function csp_nonce(): string {
  if (empty($_SESSION['csp_nonce'])) {
    $_SESSION['csp_nonce'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csp_nonce'];
}

// Cabeceras de seguridad (llamar al inicio de cada página)
function send_security_headers(): void {
  $nonce  = csp_nonce();
  $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

  if ($secure) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
  }
  header('X-Frame-Options: SAMEORIGIN');
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: strict-origin-when-cross-origin');
  header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
  header('Cross-Origin-Opener-Policy: same-origin');

  // Allow self + nonce; plus CDNs usados (Google Fonts, jsDelivr)
  $csp = "default-src 'self'; "
       . "img-src 'self' data:; "
       . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
       . "font-src 'self' data: https://fonts.gstatic.com; "
       . "script-src 'self' 'nonce-$nonce' https://cdn.jsdelivr.net; "
       . "base-uri 'self'; form-action 'self'; frame-ancestors 'self';";
  header("Content-Security-Policy: $csp");
}

// Validación de POST requerida
function require_post_fields(array $fields): array {
  $data = [];
  foreach ($fields as $f) {
    if (!isset($_POST[$f])) throw new RuntimeException('Missing field: '.$f);
    $data[$f] = trim((string)$_POST[$f]);
  }
  return $data;
}

// === Password helpers (hash/verify) ===============================

// Hash seguro con Argon2id si está disponible; fallback a bcrypt
if (!function_exists('password_hash_secure')) {
  function password_hash_secure(string $password): string {
    if (defined('PASSWORD_ARGON2ID')) {
      $options = ['memory_cost' => 1 << 17, 'time_cost' => 4, 'threads' => 2];
      return password_hash($password, PASSWORD_ARGON2ID, $options);
    }
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
  }
}

if (!function_exists('password_verify_secure')) {
  function password_verify_secure(string $password, string $hash): bool {
    return password_verify($password, $hash);
  }
}

if (!function_exists('password_needs_rehash_secure')) {
  function password_needs_rehash_secure(string $hash): bool {
    if (defined('PASSWORD_ARGON2ID')) {
      $opts = ['memory_cost' => 1 << 17, 'time_cost' => 4, 'threads' => 2];
      return password_needs_rehash($hash, PASSWORD_ARGON2ID, $opts);
    }
    return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
  }
}
