<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once LIB_DIR . '/security.php';
require_once LIB_DIR . '/csrf.php';
require_once LIB_DIR . '/db.php';
require_once LIB_DIR . '/auth.php';

send_security_headers();
header('Content-Type: application/json; charset=utf-8');

// Solo POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'method']);
  exit;
}

// Autenticación
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'auth']);
  exit;
}

// CSRF (token por cabecera o POST)
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? null);
if (function_exists('csrf_validate') && !csrf_validate($token)) {
  http_response_code(419);
  echo json_encode(['ok' => false, 'error' => 'csrf']);
  exit;
}

// Cargar datos (JSON o form-data)
$raw = file_get_contents('php://input');
$payload = $raw ? json_decode($raw, true) : $_POST;
if (!is_array($payload)) $payload = [];

$module  = strtolower(trim((string)($payload['module'] ?? '')));
$score   = (int)($payload['score'] ?? 0);
$total   = (int)($payload['total'] ?? 0);
$passed  = (int)($payload['passed'] ?? 0);
$userId  = (int)$_SESSION['user_id'];

if (!in_array($module, ['basic','intermediate','advanced'], true) || $total <= 0 || $score < 0 || $score > $total) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'bad_input']);
  exit;
}

$percent = round(($score / $total) * 100, 2);

try {
  db_exec(
    'INSERT INTO quiz_results (user_id, module, score, total, percent, passed) VALUES (?,?,?,?,?,?)',
    [$userId, $module, $score, $total, $percent, $passed ? 1 : 0]
  );
  echo json_encode(['ok' => true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'db']);
}
