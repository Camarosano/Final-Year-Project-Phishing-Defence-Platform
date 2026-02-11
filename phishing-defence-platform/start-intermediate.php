<?php
/**
 * Intermediate module starter (secure version)
 * - Loads the intermediate question set
 * - Resets module state in session
 * - Redirects to the module runner
 *
 * Security notes:
 * - Session is configured/started in config.php (no raw session_start here)
 * - No output before header() to avoid "headers already sent"
 * - Avoid leaking internal details on failure
 */

// === Global security and utility includes ===
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';

// Optional but consistent: send security headers even if there's no HTML output
send_security_headers();

// --- Load questions safely ---
require_once __DIR__ . '/questions-intermediate.php';

if (!isset($intermediate_questions) || !is_array($intermediate_questions) || count($intermediate_questions) === 0) {
  // Fail closed without exposing internals
  http_response_code(500);
  exit;
}

// --- Reset module session state ---


$_SESSION['questions']        = $intermediate_questions; // Array of questions
$_SESSION['current_question'] = 0;                        // Start from first question
$_SESSION['answers']          = [];                       // Clear previous answers

// --- Redirect to the module runner ---
header('Location: module-intermediate.php');
exit;