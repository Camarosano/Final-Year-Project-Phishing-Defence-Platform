<?php
declare(strict_types=1);

// === Includes ===
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

// Seguridad + nonce + auth
send_security_headers();
$nonce = csp_nonce();
require_auth();

$userId     = (int)($_SESSION['user_id'] ?? 0);
$modulesMax = ['basic' => 10, 'intermediate' => 15, 'advanced' => 15];
$yMax       = max($modulesMax); // para escalar Y

// (Opcional) Borrar resultados (reset real) con CSRF
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['wipe'])) {
  $token = $_POST['csrf_token'] ?? null;
  if (function_exists('csrf_validate') && csrf_validate($token)) {
    try {
      db_exec('DELETE FROM quiz_results WHERE user_id = ?', [$userId]);
      unset($_SESSION['basic_history'], $_SESSION['intermediate_history'], $_SESSION['advanced_history']);
    } catch (Throwable $e) { /* silencioso */ }
  }
  header('Location: ' . url('performance.php'));
  exit;
}

// Leer intentos
$rows = [];
try {
  $rows = db_query(
    'SELECT module, score, total, percent, passed, taken_at
       FROM quiz_results
      WHERE user_id = ?
   ORDER BY taken_at ASC, id ASC',
    [$userId]
  ) ?: [];
} catch (Throwable $e) {
  $rows = [];
}

// Histórico por módulo para el gráfico
$hist = ['basic' => [], 'intermediate' => [], 'advanced' => []];
foreach ($rows as $r) {
  $m = $r['module'];
  if (isset($hist[$m])) $hist[$m][] = (int)$r['score'];
}

// Stats helper
function stats(array $arr): array {
  if (!$arr) return ['best' => 0, 'avg' => 0, 'count' => 0];
  return [
    'best'  => max($arr),
    'avg'   => round(array_sum($arr) / count($arr), 2),
    'count' => count($arr),
  ];
}

// Series {x: intento, y: score}
$chartSeries = [];
foreach ($hist as $mod => $scores) {
  $points = [];
  foreach ($scores as $i => $v) $points[] = ['x' => $i + 1, 'y' => $v];
  $chartSeries[$mod] = $points;
}

// CSRF para wipe
$csrf = function_exists('csrf_token') ? csrf_token() : ($_SESSION['csrf_token'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Performance – Phishing Defence</title>
<link rel="stylesheet" href="../css/dashboard.css">
<style>
  main{ padding:96px 24px 48px; color:#e5e7eb; max-width:1100px; margin:0 auto; }
  h1{ margin:0 0 18px; color:#e11d48; }
  .cards{ display:grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-bottom:22px; }
  .card{ background:#111; border:2px solid #e11d48; border-radius:14px; padding:16px 18px; box-shadow:0 10px 24px rgba(0,0,0,.35); }
  .card h3{ margin:0 0 8px; color:#f87171; }
  .metric{ font-size:1.4rem; color:#22c55e; }
  .metric small{ display:block; font-size:.8rem; color:#9ca3af; }
  .reset-wrap{ text-align:center; margin-top:16px; }
  .reset{ background:#6b7280; color:#fff; border:0; padding:10px 16px; border-radius:10px; cursor:pointer; }
  .reset:hover{ filter:brightness(1.05); }
  canvas{ background:#0b0b0b; border:2px solid #e11d48; border-radius:14px; padding:8px; }
</style>
</head>
<body>

<?php include 'sidebar-and-topbar.inc.php'; ?>

<main>
  <h1>Your overall performance</h1>

  <!-- Stats -->
  <div class="cards">
    <?php foreach ($modulesMax as $mod => $max):
      $s = stats($hist[$mod] ?? []); ?>
      <div class="card">
        <h3><?= ucfirst($mod) ?> module</h3>
        <div class="metric"><?= (int)$s['best'] ?> / <?= (int)$max ?><small>best</small></div>
        <div class="metric" style="font-size:1.1rem;">
          <?= number_format((float)$s['avg'], 2) ?> avg &nbsp;·&nbsp; <?= (int)$s['count'] ?> attempts
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Chart -->
  <canvas id="perfChart" height="120"></canvas>

  <!-- Wipe -->
  <div class="reset-wrap">
    <?php if (!empty($rows)): ?>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <button class="reset" name="wipe" value="1">Clear my results</button>
      </form>
    <?php endif; ?>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script nonce="<?= $nonce ?>">
// Series { x: attempt, y: score }
const series = <?= json_encode($chartSeries, JSON_UNESCAPED_SLASHES) ?>;
const yMax   = <?= (int)$yMax ?>;

// Colores
const colours = {
  basic:        'rgb(34,197,94)',
  intermediate: 'rgb(240,171,0)',
  advanced:     'rgb(225,29,72)'
};

// Datasets
const datasets = Object.keys(series).map(mod => ({
  label: mod.charAt(0).toUpperCase() + mod.slice(1),
  data: series[mod],           // [{x:1,y:..}, {x:2,y:..}]
  borderColor: colours[mod],
  backgroundColor: colours[mod],
  tension: 0.3,
  fill: false,
  spanGaps: false
}));

// Gráfico con eje X lineal (intentos) y parsing desactivado
const ctx = document.getElementById('perfChart');
new Chart(ctx, {
  type: 'line',
  data: { datasets },
  options: {
    parsing: false, // Usamos {x,y} tal cual
    plugins: {
      legend: { labels: { color: '#f8fafc' } }
    },
    scales: {
      x: {
        type: 'linear',
        min: 1,
        ticks: { stepSize: 1, color: '#f8fafc' },
        title: { display: true, text: 'Attempt', color: '#f8fafc' }
      },
      y: {
        beginAtZero: true,
        suggestedMax: yMax,
        ticks: { color: '#f8fafc' },
        title: { display: true, text: 'Score', color: '#f8fafc' }
      }
    }
  }
});
</script>
</body>
</html>
