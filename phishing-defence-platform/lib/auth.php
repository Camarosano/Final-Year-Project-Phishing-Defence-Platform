<?php
declare(strict_types=1);

// Solo helpers de sesión / acceso / rate-limiting.
// (Las funciones de password están en lib/security.php)
require_once __DIR__ . '/security.php';

/** Asegura que la sesión esté iniciada (evita repetir código) */
if (!function_exists('auth_ensure_session')) {
  function auth_ensure_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
  }
}

/** Guardar estado de usuario tras login correcto */
if (!function_exists('start_user_session')) {
  function start_user_session(array $user): void {
    auth_ensure_session();
    session_regenerate_id(true);
    $_SESSION['user_id']  = isset($user['id']) ? (int)$user['id'] : 0;
    $_SESSION['username'] = (string)($user['username'] ?? $user['email'] ?? '');
    $_SESSION['auth_ts']  = time();
    reset_login_attempts();
  }
}

/** ¿Hay usuario autenticado? */
if (!function_exists('is_logged_in')) {
  function is_logged_in(): bool {
    auth_ensure_session();
    return !empty($_SESSION['user_id']);
  }
}

/** Exigir autenticación: si no hay sesión, redirige a login */
if (!function_exists('require_auth')) {
  function require_auth(): void {
    if (!is_logged_in()) {
      $target = 'login.php?error=auth';
      if (function_exists('app_url')) {
        $target = app_url('login.php?error=auth');
      }
      header('Location: ' . $target, true, 302);
      exit;
    }
  }
}

/** Exigir NO estar autenticado (para páginas login/signup) */
if (!function_exists('require_guest')) {
  function require_guest(): void {
    if (is_logged_in()) {
      $target = 'dashboard.php';
      if (function_exists('app_url')) {
        $target = app_url('dashboard.php');
      }
      header('Location: ' . $target, true, 302);
      exit;
    }
  }
}

/** Cerrar sesión de forma segura */
if (!function_exists('logout')) {
  function logout(): void {
    auth_ensure_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $p = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
  }
}

/** ---- Rate limiting (intentos de login) ----------------------- */

if (!function_exists('login_rate_limited')) {
  function login_rate_limited(): bool {
    auth_ensure_session();
    $max    = 5;     // intentos permitidos
    $window = 300;   // en segundos (5 min)
    $now    = time();

    $bucket = $_SESSION['login_rl'] ?? ['count' => 0, 'ts' => $now];

    // reset de ventana si expiró
    if (($now - (int)$bucket['ts']) > $window) {
      $bucket = ['count' => 0, 'ts' => $now];
    }
    $_SESSION['login_rl'] = $bucket;

    return (int)$bucket['count'] >= $max;
  }
}

if (!function_exists('note_login_attempt')) {
  function note_login_attempt(): void {
    auth_ensure_session();
    $now    = time();
    $bucket = $_SESSION['login_rl'] ?? ['count' => 0, 'ts' => $now];
    if (($now - (int)$bucket['ts']) > 300) {
      $bucket = ['count' => 0, 'ts' => $now];
    }
    $bucket['count'] = ((int)$bucket['count']) + 1;
    $_SESSION['login_rl'] = $bucket;
  }
}

if (!function_exists('reset_login_attempts')) {
  function reset_login_attempts(): void {
    auth_ensure_session();
    unset($_SESSION['login_rl']);
  }
}

/** Datos del usuario actual (por conveniencia) */
if (!function_exists('current_user_id')) {
  function current_user_id(): ?int {
    auth_ensure_session();
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
  }
}

if (!function_exists('current_username')) {
  function current_username(): ?string {
    auth_ensure_session();
    return $_SESSION['username'] ?? null;
  }
}
