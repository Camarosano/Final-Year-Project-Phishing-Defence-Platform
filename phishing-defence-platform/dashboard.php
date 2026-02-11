<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

send_security_headers();
$nonce = csp_nonce();
require_auth();

$userId   = (int)($_SESSION['user_id'] ?? 0);
$username = htmlspecialchars($_SESSION['username'] ?? 'user', ENT_QUOTES);

// Lee intentos de la BD
$rows = db_query(
  'SELECT module, score, total, passed, taken_at
     FROM quiz_results
    WHERE user_id = ?
 ORDER BY taken_at ASC, id ASC',
  [$userId]
) ?: [];

// Construye histórico y flags de aprobado
$hist   = ['basic'=>[], 'intermediate'=>[], 'advanced'=>[]];
$passed = ['basic'=>false, 'intermediate'=>false, 'advanced'=>false];

foreach ($rows as $r) {
  $m = $r['module'];
  if (isset($hist[$m])) {
    $hist[$m][] = (int)$r['score'];
    if ((int)$r['passed'] === 1) $passed[$m] = true;
  }
}

// Desbloqueos por progreso
$unlockIntermediate = $passed['basic'];
$unlockAdvanced     = $passed['intermediate'];

// Sincroniza las flags de sesión para compatibilidad con otras páginas
$_SESSION['basic_passed']        = $passed['basic'];
$_SESSION['intermediate_passed'] = $passed['intermediate'];
$_SESSION['advanced_passed']     = $passed['advanced'];

// Máximos por módulo (para escala de charts si quieres)
$maxPerModule = ['basic'=>10, 'intermediate'=>15, 'advanced'=>15];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard – Phishing Defence</title>
<link rel="stylesheet" href="../css/dashboard.css">
<style>
  main{padding:96px 24px 48px; color:#e5e7eb; max-width:1100px; margin:0 auto;}
  h1{margin:0 0 8px; color:#22c55e;}
  .subtitle{margin:0 0 22px; color:#9ca3af}
  .grid{display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:18px; margin-bottom:24px;}
  .mod{background:#0b0b0b; border:2px solid #e11d48; border-radius:14px; padding:18px; box-shadow:0 10px 24px rgba(0,0,0,.35);}
  .mod h2{margin:0 0 4px; color:#22c55e;}
  .mod p{margin:8px 0 16px; color:#9ca3af; min-height:38px;}
  .btn{display:inline-block; background:#e11d48; color:#fff; padding:10px 16px; border-radius:10px; text-decoration:none; font-weight:700;}
  .btn:focus-visible{outline:2px solid #22c55e; outline-offset:2px;}
  .btn[disabled]{opacity:.5; cursor:not-allowed; background:#4b5563;}
  .mini{background:#111; border:2px solid #e11d48; border-radius:12px; padding:10px;}
  .mini h3{margin:0 0 6px; color:#f87171;}
</style>
</head>
<body>

<?php include 'sidebar-and-topbar.inc.php'; ?>

<main>
  <h1>Welcome to your Dashboard, <?= $username ?>!</h1>
  <p class="subtitle">Select a training module to begin or continue your journey.</p>

  <!-- Tarjetas de módulos -->
  <div class="grid">
    <div class="mod">
      <h2>Basic</h2>
      <p>Learn the fundamentals and stay safe online.</p>
      <a class="btn" href="start-basic.php">Start / Retry</a>
    </div>

    <div class="mod" style="<?= $unlockIntermediate? '' : 'opacity:.6;' ?>">
      <h2>Intermediate</h2>
      <p>Dive deeper into phishing techniques.</p>
      <?php if ($unlockIntermediate): ?>
        <a class="btn" href="start-intermediate.php">Start / Retry</a>
      <?php else: ?>
        <button class="btn" disabled>Complete Basic to unlock</button>
      <?php endif; ?>
    </div>

    <div class="mod" style="<?= $unlockAdvanced? '' : 'opacity:.6;' ?>">
      <h2>Advanced</h2>
      <p>Master detection and defence.</p>
      <?php if ($unlockAdvanced): ?>
        <a class="btn" href="start-advanced.php">Start / Retry</a>
      <?php else: ?>
        <button class="btn" disabled>Complete Intermediate to unlock</button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Mini-gráficas de progreso -->
  <div class="grid">
    <div class="mini">
      <h3>Basic</h3>
      <canvas id="mini-basic" height="90"></canvas>
    </div>
    <div class="mini">
      <h3>Intermediate</h3>
      <canvas id="mini-intermediate" height="90"></canvas>
    </div>
    <div class="mini">
      <h3>Advanced</h3>
      <canvas id="mini-advanced" height="90"></canvas>
    </div>
  </div>
</main>

<!-- Chart.js permitido por tu CSP -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script nonce="<?= $nonce ?>">
const series = <?= json_encode($hist, JSON_UNESCAPED_SLASHES) ?>;
const maxes  = <?= json_encode($maxPerModule) ?>;

const colours = {
  basic:        'rgb(34,197,94)',
  intermediate: 'rgb(240,171,0)',
  advanced:     'rgb(225,29,72)'
};

function sparkline(id, mod){
  const data = series[mod] || [];
  const el = document.getElementById(id);
  if (!el) return;
  new Chart(el, {
    type: 'line',
    data: {
      labels: data.map((_,i)=> i+1),
      datasets: [{
        data,
        borderColor: colours[mod],
        backgroundColor: colours[mod],
        pointRadius: 0,
        tension: .3,
        fill: false,
        borderWidth: 2
      }]
    },
    options: {
      plugins: { legend: { display:false } },
      scales: {
        x: { display:false },
        y: {
          beginAtZero: true,
          suggestedMax: maxes[mod] || 10,
          ticks: { color: '#f8fafc', stepSize: 1 },
          grid: { color: 'rgba(148,163,184,.15)' }
        }
      }
    }
  });
}

sparkline('mini-basic','basic');
sparkline('mini-intermediate','intermediate');
sparkline('mini-advanced','advanced');
</script>
</body>
</html>
