<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once LIB_DIR . '/security.php';
require_once LIB_DIR . '/db.php';
require_once LIB_DIR . '/auth.php';

send_security_headers();
header('Content-Type: application/json; charset=utf-8');

// Autenticación
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'auth']);
  exit;
}

// Puedes limitar por módulo o devolver todo
$module = isset($_GET['module']) ? strtolower(trim((string)$_GET['module'])) : null;
$params = [$userId];
$where  = ' WHERE user_id = ? ';

if ($module && in_array($module, ['basic','intermediate','advanced'], true)) {
  $where .= ' AND module = ? ';
  $params[] = $module;
}

try {
  $rows = db_query(
    'SELECT module, score, total, percent, passed, taken_at
       FROM quiz_results ' . $where . '
   ORDER BY taken_at DESC
      LIMIT 100',
    $params
  );

  echo json_encode(['ok' => true, 'data' => $rows ?: []]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'db']);
}
