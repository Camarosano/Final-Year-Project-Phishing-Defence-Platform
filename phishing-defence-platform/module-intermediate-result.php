<?php
declare(strict_types=1);

// === Global security and utility includes ===
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

// === Send security headers + generate CSP nonce ===
send_security_headers();
$nonce = csp_nonce();
require_auth(); // ← asegura sesión iniciada

/******************************************************************
 *  module-intermediate-result.php
 *  --------------------------------------------------------------
 *  • +1 por correcta, –0.25 por extra (checkbox)
 *  • Umbral de aprobado: 70% del total
 ******************************************************************/

// Debe venir de la ejecución del test
if (!isset($_SESSION['inter_questions'], $_SESSION['inter_ans'])) {
    header('Location: ' . url('dashboard.php'));
    exit;
}

$questions = $_SESSION['inter_questions'];
$answers   = $_SESSION['inter_ans'];
$total     = count($questions);   // p.ej. 15

$score = 0.0;

/* ----------- corrección ----------- */
foreach ($questions as $i => $q) {
    $userRaw = $answers[$i] ?? ($q['type'] === 'checkbox' ? [] : -1);

    if ($q['type'] === 'checkbox') {
        $u = array_map('intval', (array)$userRaw);
        $c = array_map('intval', $q['answer']);
        sort($u); sort($c);

        if ($u === $c) {
            $score += 1;
        } else {
            // penaliza checks extra que no están en la solución
            $score -= 0.25 * count(array_diff($u, $c));
        }

    } else { /* radio / boolean */
        $user = (int)$userRaw;
        $corr = (int)$q['answer'];

        if ($user === $corr) {
            $score += 1;
        } elseif ($user !== -1) {
            $score -= 0.25;
        }
    }
}

// clamp
if ($score < 0)       $score = 0;
if ($score > $total)  $score = (float)$total;

/* ----------- aprobado ----------- */
$threshold = 0.70 * max(1, $total);       // 70%
$passed    = ($score >= $threshold);
$percent   = round(($score / max(1, $total)) * 100, 2);

// Flags en sesión (compatibilidad)
$_SESSION['intermediate_passed'] = $passed;
if (!isset($_SESSION['intermediate_history']) || !is_array($_SESSION['intermediate_history'])) {
    $_SESSION['intermediate_history'] = [];
}
$_SESSION['intermediate_history'][] = round($score, 2);

/* ===== Guardar intento en BD (para Chart.js) ===== */
$userId = (int)($_SESSION['user_id'] ?? 0);
$module = 'intermediate';

// Evita duplicados al refrescar (huella del intento)
$attemptKey = hash('sha256', json_encode($answers, JSON_UNESCAPED_UNICODE) . "|$module|$total");
if (($_SESSION['last_saved_attempt_intermediate'] ?? '') !== $attemptKey) {
    try {
        db_exec(
            'INSERT INTO quiz_results (user_id, module, score, total, percent, passed)
             VALUES (?,?,?,?,?,?)',
            [$userId, $module, (int)round($score), $total, $percent, $passed ? 1 : 0]
        );
        $_SESSION['last_saved_attempt_intermediate'] = $attemptKey;
    } catch (Throwable $e) {
        // Silencioso para el usuario final (puedes loguear si quieres)
        // error_log('quiz_results insert (intermediate) failed: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Result – Intermediate module</title>
<style>
  body{
    background:#0b0b0b; color:#e5e7eb;
    font-family:Arial,Helvetica,sans-serif;
    display:flex; align-items:center; justify-content:center;
    min-height:100vh; margin:0;
  }
  .card{
    background:#111; border:2px solid #e11d48; border-radius:14px;
    box-shadow:0 12px 24px rgba(0,0,0,.35);
    padding:2.2rem 3rem; text-align:center; max-width:560px; width:92%;
  }
  h1{ margin-top:0; color:#e11d48; }
  .score{ font-size:2.4rem; margin:16px 0; color:#22c55e; }
  .passed{ color:#22c55e; font-weight:bold; }
  .failed{ color:#f87171; font-weight:bold; }
  .buttons a{
    display:inline-block; margin:10px 8px; padding:10px 24px;
    border-radius:8px; text-decoration:none; font-size:15px; transition:transform .1s ease;
  }
  .buttons a:hover{ transform:translateY(-1px); }
  .retry{ background:#222; color:#e5e7eb; border:1px solid #374151; }
  .dash { background:#6b7280; color:#fff; }
  .next { background:#2563eb; color:#fff; }
</style>
</head>
<body>

<div class="card">
   <h1>Module: Intermediate</h1>
   <div class="score"><?= number_format($score, 2) ?> / <?= (int)$total ?></div>

   <?php if ($passed): ?>
        <p class="passed">✅ You passed! (<?= $percent ?>%)</p>
   <?php else: ?>
        <p class="failed">❌ You did not pass. (<?= $percent ?>%)</p>
   <?php endif; ?>

   <div class="buttons">
        <a class="retry" href="<?= e(url('start-intermediate.php')) ?>">Retry test</a>
        <a class="dash"  href="<?= e(url('dashboard.php')) ?>">Dashboard</a>
        <?php if ($passed): ?>
            <a class="next" href="<?= e(url('start-advanced.php')) ?>">Next level</a>
        <?php endif; ?>
   </div>
</div>

</body>
</html>
