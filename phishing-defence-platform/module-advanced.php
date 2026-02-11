<?php
declare(strict_types=1);

// Global security and utility includes
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
require_once __DIR__ . '/lib/csrf.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';

// Security headers + CSP nonce
send_security_headers();
$nonce = csp_nonce();

// Load questions or redirect to the start
if (!isset($_SESSION['adv_questions'])) {
    header('Location: start-advanced.php');
    exit;
}

$Q     = $_SESSION['adv_questions'];
$total = count($Q);

// Initialise per-attempt session state
if (!isset($_SESSION['adv_idx'])) {
    $_SESSION['adv_idx'] = 0;
    $_SESSION['adv_ans'] = array_fill(0, $total, null);
}
$idx    = $_SESSION['adv_idx'];
$q      = $Q[$idx];
$multi  = ($q['type'] === 'checkbox');
$rank   = ($q['type'] === 'rank');
$stored = $_SESSION['adv_ans'][$idx] ?? ($rank ? [] : ($multi ? [] : null));

/* POST handler */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (array_key_exists('answer', $_POST)) {
        $_SESSION['adv_ans'][$idx] = $_POST['answer'];
    }

    if (isset($_POST['next'])) {
        if ($idx < $total - 1) {
            $_SESSION['adv_idx']++;
        } else {
            // Go to review (not directly to result)
            header('Location: module-advanced-review.php');
            exit;
        }
    }

    if (isset($_POST['previous']) && $idx > 0) {
        $_SESSION['adv_idx']--;
    }

    header('Location: module-advanced.php');
    exit;
}

/* Image helper */
$imgSrc = !empty($q['image']) ? '../img/' . basename($q['image']) . '?v=' . $idx : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Module Advanced</title>
<style>
body{background:#f5f6f8;font-family:Arial,Helvetica,sans-serif;margin:0}
.card{max-width:760px;margin:7% auto;background:#fff;border-radius:14px;
      padding:2rem;box-shadow:0 8px 16px rgba(0,0,0,.08)}
img.preview{max-width:100%;border-radius:12px;margin:22px 0}
label{display:block;margin:.48rem 0}
.rank-row{display:flex;align-items:center;gap:.6rem;margin:.4rem 0}
select{padding:.2rem .4rem;border:1px solid #cbd5e1;border-radius:4px}
.btn{border:0;border-radius:6px;padding:10px 18px;font-size:14px;cursor:pointer}
.next{background:#2563eb;color:#fff}
.prev{background:#e5e7eb;color:#111}
.top{position:absolute;top:18px;right:30px;font-size:14px}
.top a{color:#2563eb;text-decoration:none}
</style>
</head>
<body>

<div class="top"><a href="module-advanced-review.php">Finish test now?</a></div>

<div class="card">
<form method="post" action="module-advanced.php">

<h3>Question <?= $idx + 1 ?> of <?= $total ?></h3>
<p><?= htmlspecialchars($q['question']) ?></p>

<?php if ($imgSrc): ?>
  <img src="<?= $imgSrc ?>" class="preview" alt="question media">
<?php endif; ?>

<?php if ($rank): /* RANK format */ ?>
  <?php $n = count($q['options']); foreach ($q['options'] as $i => $opt): ?>
    <div class="rank-row">
       <?= htmlspecialchars($opt) ?>
       <select name="answer[<?= $i ?>]">
          <option value="">--</option>
          <?php for ($k = 1; $k <= $n; $k++):
                $sel = (isset($stored[$i]) && (string)$stored[$i] === (string)$k) ? 'selected' : ''; ?>
            <option value="<?= $k ?>" <?= $sel ?>><?= $k ?></option>
          <?php endfor; ?>
       </select>
    </div>
  <?php endforeach; ?>

<?php else: foreach ($q['options'] as $i => $opt):
        if ($multi) {
            $checked = is_array($stored) && in_array((string)$i, $stored, true);
            $type = 'checkbox'; $name = 'answer[]';
        } else {
            $checked = ($stored !== null && (string)$stored === (string)$i);
            $type = 'radio';    $name = 'answer';
        } ?>
    <label>
      <input type="<?= $type ?>" name="<?= $name ?>" value="<?= $i ?>" <?= $checked ? 'checked' : '' ?>>
      <?= htmlspecialchars($opt) ?>
    </label>
<?php endforeach; endif; ?>

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
