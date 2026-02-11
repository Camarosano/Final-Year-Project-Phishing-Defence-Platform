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

/* ---------- Check that questions are loaded ---------- */
if (!isset($_SESSION['questions']) || !is_array($_SESSION['questions'])) {
    echo 'No questions loaded.'; // Fallback if no questions exist in session
    exit;
}

$questions      = $_SESSION['questions'];       // Full array of quiz questions
$totalQuestions = count($questions);            // Total number of questions in the module

// If it's the first time loading this page, initialize progress and answer storage
if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0;                       // Start at question 0
    $_SESSION['answers']          = array_fill(0, $totalQuestions, null); // Prepare empty answers array
}

$idx     = $_SESSION['current_question'];       // Current question index
$q       = $questions[$idx];                    // Current question object
$isMulti = ($q['type'] === 'checkbox');         // Boolean: is it a multi-answer question?
$stored  = $_SESSION['answers'][$idx] ?? ($isMulti ? [] : null); // Stored response if any

/* ---------- Handle POST form submissions ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Save the selected answer
    if (array_key_exists('answer', $_POST)) {
        $_SESSION['answers'][$idx] = $_POST['answer']; // Store answer (array or string)
    }

    // Handle navigation: "Next" button
    if (isset($_POST['next'])) {
        if ($idx < $totalQuestions - 1) {
            $_SESSION['current_question']++; // Move to next question
        } else {
            // End of module → redirect to review page
            header('Location: module-basic-review.php');
            exit;
        }
    }

    // Handle navigation: "Previous" button
    if (isset($_POST['previous']) && $idx > 0) {
        $_SESSION['current_question']--; // Go back one question
    }

    // Refresh to reload with updated index/answer
    header('Location: module-basic.php');
    exit;
}

/* ---------- Optional image (if present in question) ---------- */
$imgSrc = '';
if (!empty($q['image'])) {
    // Add cache-busting suffix using question index
    $imgSrc = '../img/' . basename($q['image']) . '?v=' . $idx;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Module Basic</title>
<style>
  /* Basic styling for question card UI */
  body {
    background: #f5f6f8;
    font-family: Arial, Helvetica, sans-serif;
  }
  .card {
    max-width: 700px;
    margin: 7% auto;
    background: #fff;
    border-radius: 14px;
    padding: 2rem;
    box-shadow: 0 8px 16px rgba(0,0,0,.08);
  }
  .img-box {
    text-align: center;
    margin: 22px 0;
  }
  .img-box img {
    max-width: 100%;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,.06);
  }
  label {
    display: block;
    margin: .48rem 0;
  }
  .btn {
    border: 0;
    border-radius: 6px;
    padding: 10px 18px;
    font-size: 14px;
    cursor: pointer;
  }
  .next {
    background: #2563eb;
    color: #fff;
  }
  .prev {
    background: #e5e7eb;
    color: #111;
  }
  .top {
    position: absolute;
    top: 18px;
    right: 30px;
    font-size: 14px;
  }
  .top a {
    color: #2563eb;
    text-decoration: none;
  }
</style>
</head>
<body>

<!-- Shortcut to allow users to finish the module early and go to the review screen -->
<div class="top"><a href="module-basic-review.php">Finish test now?</a></div>

<div class="card">
<form method="post" action="module-basic.php">
   <h3>Question <?= $idx + 1 ?> of <?= $totalQuestions ?></h3>
   <p><?= htmlspecialchars($q['question']) ?></p>

   <!-- Show image if available -->
   <?php if ($imgSrc): ?>
      <div class="img-box"><img src="<?= $imgSrc ?>" alt="phishing sample"></div>
   <?php endif; ?>

   <!-- Render answer options -->
   <?php foreach ($q['options'] as $i => $opt): ?>
      <?php
        // Determine field type and value
        if ($isMulti) {
            $checked = is_array($stored) && in_array("$i", $stored); // For checkbox
            $type    = 'checkbox';
            $name    = 'answer[]';
        } else {
            $checked = ($stored !== null && $stored == $i);          // For radio button
            $type    = 'radio';
            $name    = 'answer';
        }
      ?>
      <label>
        <input type="<?= $type ?>" name="<?= $name ?>" value="<?= $i ?>"
               <?= $checked ? 'checked' : '' ?>>
        <?= htmlspecialchars($opt) ?>
      </label>
   <?php endforeach; ?>

   <!-- Navigation buttons -->
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