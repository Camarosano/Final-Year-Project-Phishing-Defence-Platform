<?php
declare(strict_types=1);

// === Global security + utilities ===
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

// === Security headers + nonce ===
send_security_headers();
$nonce = csp_nonce();
require_auth();

// === Load state (questions + answers) ===
$Q   = $_SESSION['adv_questions'] ?? $_SESSION['adv_q'] ?? null;
$ans = $_SESSION['adv_ans'] ?? null;

if (!$Q || !is_array($Q)) {
    header('Location: ' . url('dashboard.php')); exit;
}
$total = count($Q);

// === Jump back to a specific question ===
if (isset($_GET['q'])) {
    $idx = (int)$_GET['q'];
    if ($idx >= 0 && $idx < $total) {
        $_SESSION['adv_idx'] = $idx;
        header('Location: module-advanced.php'); exit;
    }
}

// === Final submission (ensure all answered) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_final'])) {
    for ($i = 0; $i < $total; $i++) {
        if (!isset($ans[$i]) || $ans[$i] === null) {
            $_SESSION['adv_idx'] = $i;
            header('Location: module-advanced.php'); exit;
        }
    }
    header('Location: module-advanced-result.php'); exit;
}

// === Progress count ===
$answered = 0;
for ($i = 0; $i < $total; $i++) {
    if (isset($ans[$i]) && $ans[$i] !== null) $answered++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Review – Advanced module</title>
<link rel="stylesheet" href="../css/dashboard.css">
<style>
/* Page layout */
body{font-family:Arial,Helvetica,sans-serif;background:#f5f6f8;text-align:center}
h1{margin-top:40px}

/* Grid of question buttons */
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(80px,1fr));
      gap:14px;max-width:540px;margin:40px auto}

/* Buttons */
.box{padding:20px 0;border-radius:8px;background:#e5e7eb;color:#111;
     font-weight:bold;cursor:pointer;text-decoration:none;display:block}
.answered{background:#a7f3d0}

/* Submit button */
.btn{padding:12px 26px;font-size:15px;border:0;border-radius:6px;
     background:#2563eb;color:#fff;cursor:pointer}
.btn:disabled{opacity:.55;cursor:not-allowed}

/* Wrapper + centred submit row */
.wrapper{
  min-height:calc(100vh - 110px);
  display:flex;flex-direction:column;justify-content:center;align-items:center;gap:12px;
}
.final-submit{
  width:100%;max-width:540px;margin:10px auto 0;
  display:flex;justify-content:center;
}
</style>
</head>
<body>
<?php include 'sidebar-and-topbar.inc.php'; ?>

<main class="wrapper">
  <h1>Review your answers</h1>
  <p class="progress">
     <?= $answered ?> / <?= $total ?> answered
     <?php if ($answered < $total): ?>
        &nbsp;·&nbsp; 
     <?php endif; ?>
  </p>

  <!-- Buttons: one per question -->
  <div class="grid">
    <?php for ($i = 0; $i < $total; $i++):
          $done = isset($ans[$i]) && $ans[$i] !== null; ?>
      <a class="box <?= $done ? 'answered' : '' ?>" href="?q=<?= $i ?>">Q<?= $i + 1 ?></a>
    <?php endfor; ?>
  </div>

  <!-- Final submission (centred under the grid) -->
  <form method="post" class="final-submit">
    <button type="submit" name="submit_final" class="btn"
      <?= $answered < $total ? 'disabled aria-disabled="true"' : '' ?>>
      Submit final answers
    </button>
  </form>
</main>
</body>
</html>
