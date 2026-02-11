<?php
declare(strict_types=1);

/* =========================
 * App & DB config
 * ========================= */
$APP_ENV    = getenv('APP_ENV') ?: 'dev';
$DB_HOST    = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME    = getenv('DB_NAME') ?: 'phishing_defence';
$DB_USER    = getenv('DB_USER') ?: 'root';
$DB_PASS    = getenv('DB_PASS') ?: '';
$DB_CHARSET = 'utf8mb4';

/* Base pública (con / inicial, sin / final) */
$APP_BASE = getenv('APP_BASE') ?: '/Website-proyect/phishing-defence-platform';
$APP_BASE = '/' . trim($APP_BASE, '/'); // normaliza

/* =========================
 * Secure session settings
 * ========================= */
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_set_cookie_params([
  'lifetime' => 0,
  'path'     => $APP_BASE ?: '/',
  'domain'   => '',
  'secure'   => $https,
  'httponly' => true,
  'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
  // === Path helpers robustos ===
// APP_DIR: .../phishing-defence-platform
// PROJECT_ROOT: .../Website-proyect
if (!defined('APP_DIR'))      define('APP_DIR', dirname(__DIR__));
if (!defined('PROJECT_ROOT')) define('PROJECT_ROOT', dirname(APP_DIR));

// Localiza /lib en raíz del proyecto o, si no, dentro del submódulo
if (!defined('LIB_DIR')) {
  $candidates = [
    PROJECT_ROOT . DIRECTORY_SEPARATOR . 'lib',      // C:\xampp\htdocs\Website-proyect\lib
    APP_DIR      . DIRECTORY_SEPARATOR . 'lib',      // C:\xampp\htdocs\Website-proyect\phishing-defence-platform\lib
  ];
  foreach ($candidates as $c) {
    if (is_file($c . DIRECTORY_SEPARATOR . 'db.php')) {
      define('LIB_DIR', $c);
      break;
    }
  }
  if (!defined('LIB_DIR')) {
    // Mensaje claro si no encuentra la carpeta
    http_response_code(500);
    echo "LIB_DIR not found. Checked:<br>" .
         htmlspecialchars($candidates[0]) . "<br>" .
         htmlspecialchars($candidates[1]);
    exit;
  }
}
}

/* =========================
 * Path helpers (filesystem)
 * Estas constantes evitan problemas al incluir /lib desde cualquier página.
 * ========================= */
if (!defined('APP_DIR'))      define('APP_DIR', dirname(__DIR__));                  // .../phishing-defence-platform
if (!defined('PROJECT_ROOT')) define('PROJECT_ROOT', dirname(APP_DIR));             // .../Website-proyect
if (!defined('LIB_DIR'))      define('LIB_DIR', PROJECT_ROOT . DIRECTORY_SEPARATOR . 'lib'); // .../Website-proyect/lib

/* =========================
 * Error policy
 * ========================= */
if ($APP_ENV === 'prod') {
  error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
  ini_set('display_errors', '0');
} else {
  error_reporting(E_ALL);
  ini_set('display_errors', '1');
}

/* =========================
 * URL helpers (web)
 * ========================= */
function is_https(): bool {
  return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

function app_origin(): string {
  $scheme = is_https() ? 'https' : 'http';
  $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
  return $scheme . '://' . $host;
}

function url(string $path = ''): string {
  global $APP_BASE;
  $path = ltrim($path, '/');
  $base = rtrim($APP_BASE, '/');
  return app_origin() . ($base ? $base : '') . ($path ? '/' . $path : '');
}

function app_url(string $path = ''): string {
  global $APP_BASE;
  $path = ltrim($path, '/');
  $base = rtrim($APP_BASE, '/');
  return ($base ? $base : '') . ($path ? '/' . $path : '');
}

function redirect(string $path): never {
  header('Location: ' . url($path), true, 302);
  exit;
}
