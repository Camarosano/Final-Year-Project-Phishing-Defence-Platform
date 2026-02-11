<?php
declare(strict_types=1);

/**
 * /phishing-defence-platform/php/logout.php
 * Base: /Website-proyect/phishing-defence-platform
 * Redirige a login en la misma base.
 */

require_once __DIR__ . '/../config/config.php';

// Cargar libs (si existen) para send_security_headers() y logout()
$LIB = dirname(__DIR__, 2) . '/lib';
if (is_file($LIB . '/security.php')) require_once $LIB . '/security.php';
if (is_file($LIB . '/auth.php'))     require_once $LIB . '/auth.php';

// Cabeceras de seguridad opcionales
if (function_exists('send_security_headers')) send_security_headers();

// Usar helper central logout() si está disponible; si no, fallback seguro
if (function_exists('logout')) {
  logout();
} else {
  if (session_status() === PHP_SESSION_NONE) session_start();
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $p['path'] ?? '/',
      $p['domain'] ?? '',
      $p['secure'] ?? false,
      $p['httponly'] ?? true
    );
  }
  session_destroy();
}

// Redirigir al login dentro de la misma base
redirect('login.php');
