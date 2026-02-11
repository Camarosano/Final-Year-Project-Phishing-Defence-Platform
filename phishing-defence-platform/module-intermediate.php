<?php
// === Global security and utility includes ===
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

// === Send security headers + generate CSP nonce ===
send_security_headers();
$nonce = csp_nonce();

/* ---------- carga preguntas ---------- */
if (!isset($_SESSION['inter_questions']) || !is_array($_SESSION['inter_questions'])) {
    exit('No intermediate questions loaded.');
}

$Q     = $_SESSION['inter_questions'];
$total = count($Q);

if (!isset($_SESSION['inter_idx'])) {
    $_SESSION['inter_idx'] = 0;
    $_SESSION['inter_ans'] = array_fill(0, $total, null);
}

$idx    = $_SESSION['inter_idx'];
$q      = $Q[$idx];
$multi  = ($q['type'] === 'checkbox');
$stored = $_SESSION['inter_ans'][$idx] ?? ($multi ? [] : null);

/* ---------- POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* guarda respuesta */
    if (isset($_POST['answer'])) {
        $_SESSION['inter_ans'][$idx] = $_POST['answer'];
    }

    /* navegación */
    if (isset($_POST['next'])) {
        if ($idx < $total - 1) {
            $_SESSION['inter_idx'] = $idx + 1;
        } else {
            header('Location: module-intermediate-review.php');
            exit;
        }
    }

    if (isset($_POST['previous']) && $idx > 0) {
        $_SESSION['inter_idx'] = $idx - 1;
    }

    header('Location: module-intermediate.php');
    exit;
}

/* ---------- helper imagen ---------- */
$imgSrc = !empty($q['image'])
          ? '../img/' . basename($q['image']) . '?v=' . $idx
          : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Module Intermediate</title>
<style>
 body{background:#f5f6f8;font-family:Arial,Helvetica,sans-serif;margin:0}
 .card{max-width:720px;margin:7% auto;background:#fff;border-radius:14px;
       padding:2rem;box-shadow:0 8px 16px rgba(0,0,0,.08)}
 img.preview{max-width:100%;border-radius:12px;margin:22px 0}
 label{display:block;margin:.48rem 0}
 .btn{border:0;border-radius:6px;padding:10px 18px;font-size:14px;cursor:pointer}
 .next{background:#2563eb;color:#fff}
 .prev{background:#e5e7eb;color:#111}
 .top{position:absolute;top:18px;right:30px;font-size:14px}
 .top a{color:#2563eb;text-decoration:none}
</style>
</head>
<body>

<div class="top"><a href="module-intermediate-review.php">Finish test now?</a></div>

<div class="card">
<form method="post" action="module-intermediate.php">

  <h3>Question <?= $idx + 1 ?> of <?= $total ?></h3>
  <p><?= htmlspecialchars($q['question']) ?></p>

  <?php if ($imgSrc): ?>
      <img src="<?= $imgSrc ?>" alt="visual sample" class="preview">
  <?php endif; ?>

  <?php foreach ($q['options'] as $i => $opt):
        if ($multi) {
            $checked = is_array($stored) && in_array("$i", $stored);
            $type = 'checkbox'; $name = 'answer[]';
        } else {
            $checked = ($stored !== null && $stored == $i);
            $type = 'radio';    $name = 'answer';
        } ?>
      <label>
        <input type="<?= $type ?>" name="<?= $name ?>" value="<?= $i ?>"
               <?= $checked ? 'checked' : '' ?>>
        <?= htmlspecialchars($opt) ?>
      </label>
  <?php endforeach; ?>

  <div style="display:flex;justify-content:space-between;margin-top:1.6rem">
     <?php if ($idx > 0): ?>
        <button class="btn prev" name="previous" type="submit">Previous</button>
     <?php endif; ?>
     <button class="btn next" name="next" type="submit">Next</button>
  </div>

</form>
</div>

</body>
</html>