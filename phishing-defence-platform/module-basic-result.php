<?php
declare(strict_types=1);

// === Global includes ===
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

send_security_headers();
$nonce = csp_nonce();
require_auth(); // Bloquea si no hay sesión iniciada

// Debe venir de la ejecución del test
if (!isset($_SESSION['questions'], $_SESSION['answers'])) {
  header('Location: ' . url('dashboard.php'));
  exit;
}

$questions = $_SESSION['questions']; // lista completa de preguntas
$answers   = $_SESSION['answers'];   // respuestas del usuario
$total     = count($questions);

// --- Cálculo del score ---
$score = 0.0;

foreach ($questions as $i => $q) {
  $userRaw = $answers[$i] ?? ($q['type'] === 'checkbox' ? [] : -1);

  if ($q['type'] === 'checkbox') {
    $user    = array_map('intval', (array)$userRaw);
    $correct = array_map('intval', $q['answer']);

    sort($user);
    sort($correct);

    if ($user === $correct) {
      $score += 1;
    } else {
      // penaliza checks extra que no están en la solución
      $extra = array_diff($user, $correct);
      $score -= 0.25 * count($extra);
    }

  } else {
    $user    = (int)$userRaw;
    $correct = (int)$q['answer'];

    if ($user === $correct) {
      $score += 1;
    } elseif ($user !== -1) {
      $score -= 0.25;
    }
  }
}

// clamp score
if ($score < 0)       $score = 0;
if ($score > $total)  $score = (float)$total;

// aprobado: 60% o más
$passed   = ($score >= (0.6 * $total));
$percent  = round(($score / max(1, $total)) * 100, 2);
$userId   = (int)($_SESSION['user_id'] ?? 0);
$module   = 'basic';

// Guarda estado de aprobado en sesión (si lo usas en la navegación)
$_SESSION['basic_passed'] = $passed;

// (Compatibilidad con tu gráfico antiguo por sesión)
if (!isset($_SESSION['basic_history']) || !is_array($_SESSION['basic_history'])) {
  $_SESSION['basic_history'] = [];
}
$_SESSION['basic_history'][] = round($score, 2);

// --- Guardar en BD sin duplicar si se refresca ---
// Creamos una huella del intento (respuestas + módulo + total)
$attemptKey = hash('sha256', json_encode($answers, JSON_UNESCAPED_UNICODE) . "|$module|$total");

// Si no se ha guardado este intento exacto en esta sesión, insertamos
if (($_SESSION['last_saved_attempt_basic'] ?? '') !== $attemptKey) {
  try {
    db_exec(
      'INSERT INTO quiz_results (user_id, module, score, total, percent, passed)
       VALUES (?,?,?,?,?,?)',
      [$userId, $module, (int)round($score), $total, $percent, $passed ? 1 : 0]
    );
    $_SESSION['last_saved_attempt_basic'] = $attemptKey;
  } catch (Throwable $e) {
    // No mostramos detalle al usuario final; si quieres, log interno:
    // error_log('quiz_results insert failed: ' . $e->getMessage());
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Result – Basic module</title>
<style>
  body{
    background:#0b0b0b;
    color:#e5e7eb;
    font-family:Arial, Helvetica, sans-serif;
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    margin:0;
  }
  .card{
    background:#111;
    border:2px solid #e11d48;
    border-radius:14px;
    box-shadow:0 12px 24px rgba(0,0,0,.35);
    padding:2.2rem 3rem;
    text-align:center;
    max-width:560px; width:92%;
  }
  h1{ margin-top:0; color:#e11d48; }
  .score{ font-size:2.4rem; margin:16px 0; color:#22c55e; }
  .passed{ color:#22c55e; font-weight:700; }
  .failed{ color:#f87171; font-weight:700; }
  .buttons a{
    display:inline-block; margin:10px 8px; padding:10px 24px;
    border-radius:8px; text-decoration:none; font-size:15px; transition:transform .1s ease;
  }
  .buttons a:hover{ transform:translateY(-1px); }
  .retry{ background:#222; color:#e5e7eb; border:1px solid #374151; }
  .next { background:#2563eb; color:#fff; }
  .dash { background:#6b7280; color:#fff; }
</style>
</head>
<body>
  <div class="card">
    <h1>Module: Basic</h1>

    <div class="score"><?= number_format($score, 2) ?> / <?= (int)$total ?></div>

    <?php if ($passed): ?>
      <p class="passed">✅ You passed! (<?= $percent ?>%)</p>
    <?php else: ?>
      <p class="failed">❌ You did not pass. (<?= $percent ?>%)</p>
    <?php endif; ?>

    <div class="buttons">
      <a class="retry" href="<?= e(url('start-basic.php')) ?>">Retry test</a>
      <a class="dash"  href="<?= e(url('dashboard.php')) ?>">Dashboard</a>
      <?php if ($passed): ?>
        <a class="next" href="<?= e(url('start-intermediate.php')) ?>">Next level</a>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
