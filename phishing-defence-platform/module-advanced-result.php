<?php
declare(strict_types=1);

// === Global security and utility includes ===
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

// === Security headers + nonce, y exigir sesión ===
send_security_headers();
$nonce = csp_nonce();
require_auth();

/******************************************************************
 *  module-advanced-result.php
 *  --------------------------------------------------------------
 *  • +1 por respuesta correcta
 *  • –0.25 por checkbox extra o por orden de ranking incorrecto
 *  • Aprobado: 80% del total (p.ej. 12/15)
 ******************************************************************/

// Debe venir de la ejecución del test
if (!isset($_SESSION['adv_questions'], $_SESSION['adv_ans'])) {
    header('Location: ' . url('dashboard.php'));
    exit;
}

$Q      = $_SESSION['adv_questions'];   // preguntas
$Ans    = $_SESSION['adv_ans'];         // respuestas usuario
$total  = count($Q);
$score  = 0.0;

/* ---------- scoring loop ---------- */
foreach ($Q as $i => $q) {
    $type = $q['type'];
    $user = $Ans[$i] ?? (($type === 'checkbox' || $type === 'rank') ? [] : -1);
    $corr = $q['answer'];

    if ($type === 'checkbox') {
        $u = array_map('intval', (array)$user);
        $c = array_map('intval', (array)$corr);
        sort($u); sort($c);
        if ($u === $c) {
            $score += 1;
        } else {
            $score -= 0.25 * count(array_diff($u, $c)); // penaliza tildes extra
        }

    } elseif ($type === 'rank') {
        // $user: mapa itemIndex => posición; $corr: array con orden correcto de índices
        if (!empty($user) && is_array($user)) {
            // ordena por valor (posición) ascendente para obtener el orden elegido
            asort($user, SORT_NUMERIC);
            $order = array_map('intval', array_keys($user)); // orden de índices elegido
            $corr  = array_map('intval', (array)$corr);
            $score += ($order === $corr) ? 1 : -0.25;       // penaliza orden incorrecto
        }
        // si está vacío, se considera sin responder (sin penalización)

    } else { // radio / boolean
        $user = (int)$user;
        $corr = (int)$corr;
        if ($user === $corr) {
            $score += 1;
        } elseif ($user !== -1) {
            $score -= 0.25;
        }
    }
}

// Clamp
if ($score < 0)       $score = 0;
if ($score > $total)  $score = (float)$total;

/* ---------- pass / fail ---------- */
$threshold = 0.80 * max(1, $total);   // 80%
$passed    = ($score >= $threshold);
$percent   = round(($score / max(1, $total)) * 100, 2);

// Flags de sesión para navegación/compatibilidad
$_SESSION['advanced_passed'] = $passed;
if (!isset($_SESSION['advanced_history']) || !is_array($_SESSION['advanced_history'])) {
    $_SESSION['advanced_history'] = [];
}
$_SESSION['advanced_history'][] = round($score, 2);

/* ===== Guardar intento en BD (para Chart.js) ===== */
$userId = (int)($_SESSION['user_id'] ?? 0);
$module = 'advanced';

// Evitar duplicado por refresh: huella del intento
$attemptKey = hash('sha256', json_encode($Ans, JSON_UNESCAPED_UNICODE) . "|$module|$total");
if (($_SESSION['last_saved_attempt_advanced'] ?? '') !== $attemptKey) {
    try {
        db_exec(
            'INSERT INTO quiz_results (user_id, module, score, total, percent, passed)
             VALUES (?,?,?,?,?,?)',
            [$userId, $module, (int)round($score), $total, $percent, $passed ? 1 : 0]
        );
        $_SESSION['last_saved_attempt_advanced'] = $attemptKey;
    } catch (Throwable $e) {
        // Silencioso para el usuario. Opcional: error_log(...)
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Result – Advanced module</title>
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
</style>
</head>
<body>
<div class="card">
   <h1>Module: Advanced</h1>
   <div class="score"><?= number_format($score,2) ?> / <?= (int)$total ?></div>

   <?php if($passed): ?>
        <p class="passed">✅ You passed! (<?= $percent ?>%)</p>
   <?php else: ?>
        <p class="failed">❌ You did not pass. (<?= $percent ?>%)</p>
   <?php endif; ?>

   <div class="buttons">
        <a class="retry" href="<?= e(url('start-advanced.php')) ?>">Retry test</a>
        <a class="dash"  href="<?= e(url('dashboard.php')) ?>">Dashboard</a>
   </div>
</div>
</body>
</html>
