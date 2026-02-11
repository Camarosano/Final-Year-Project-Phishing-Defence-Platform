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

/* --- Basic security check --- */
// If questions are not loaded in the session, redirect to dashboard
if (!isset($_SESSION['questions'])) {
   header('Location: dashboard.php');
   exit;
}

$questions = $_SESSION['questions'];           // Load questions array from session
$total     = count($questions);                // Total number of questions
$answers   = $_SESSION['answers'] ?? [];       // Load submitted answers, or empty array if not set

/* --- Go to a specific question (via query parameter ?q=N) --- */
if (isset($_GET['q'])) {
    $q = (int)$_GET['q'];
    if ($q >= 0 && $q < $total) {
        $_SESSION['current_question'] = $q;    // Set the current question index in session
        header('Location: module-basic.php');  // Redirect to question page
        exit;
    }
}

/* --- Submit final answers and go to result page --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_final'])) {
    header('Location: module-basic-result.php'); // Final submission → go to result screen
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Review answers – Basic module</title>
<style>
  /* General layout and visual styles */
  body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f6f8;
    text-align: center;
  }

  h1 {
    margin-top: 40px;
  }

  /* Grid for question buttons */
  .grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 14px;
    max-width: 540px;
    margin: 40px auto;
  }

  /* Styling for question buttons */
  .box {
    padding: 20px 0;
    border-radius: 8px;
    background: #e5e7eb; /* default = unanswered (gray) */
    color: #111;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    display: block;
  }

  /* Highlight answered questions in light green */
  .answered {
    background: #a7f3d0;
  }

  /* Style for submit button */
  .btn {
    margin: 30px auto;
    padding: 12px 26px;
    font-size: 15px;
    border: 0;
    border-radius: 6px;
    background: #2563eb;
    color: #fff;
    cursor: pointer;
  }

  /* Ensure vertical centering in viewport */
  .wrapper {
    min-height: calc(100vh - 110px); /* Adjust for top bar height */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 12px;
  }
</style>
</head>
<body>

<main class="wrapper">
  <h1>Review your answers</h1>
  <p>
    Click any question number to go back and edit.<br>
    Green = answered &nbsp;·&nbsp; Grey = unanswered
  </p>

  <!-- Grid of buttons: one per question -->
  <div class="grid">
    <?php for ($i = 0; $i < $total; $i++): ?>
      <?php
        // Check if this question has been answered
        $class = isset($answers[$i]) && $answers[$i] !== null ? 'answered' : '';
      ?>
      <a class="box <?= $class ?>" href="?q=<?= $i ?>">
        Q<?= $i + 1 ?>
      </a>
    <?php endfor; ?>
  </div>

  <!-- Final submission button -->
  <form method="post">
    <button type="submit" name="submit_final" class="btn">Submit final answers</button>
  </form>
</main>
</body>
</html>