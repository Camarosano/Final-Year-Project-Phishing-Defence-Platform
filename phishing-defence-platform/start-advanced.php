<?php
/**
 * Advanced module starter (secure version)
 * - Loads the advanced questions set
 * - Resets session state for a fresh attempt
 * - Redirects to the module runner page
 *
 * Security notes:
 * - No direct session_start(): handled by config.php
 * - Avoids verbose error leaks to the browser
 * - Keeps all output suppressed before header() calls
 */

// === Global security and utility includes ===
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';
// CSRF not required here because this is a server-side setup trigger,
// but the receiving POST pages MUST validate CSRF tokens.

send_security_headers();

// --- Load questions safely ---
$adv_questions = require __DIR__ . '/questions-advanced.php';

// Validate structure before touching session
if (!is_array($adv_questions) || count($adv_questions) === 0) {
  // Fail closed without revealing internal details
  http_response_code(500);
  exit; // Optionally log internally instead of echoing
}

// --- Reset module session state ---

if (session_status() === PHP_SESSION_ACTIVE) {
  // session_regenerate_id(true); // uncomment if you want to rotate here too
}

// Initialize/override only the keys used by the advanced module
$_SESSION['adv_questions'] = $adv_questions;
$_SESSION['adv_idx']       = 0;
$_SESSION['adv_ans']       = array_fill(0, count($adv_questions), null);

// --- Redirect to the advanced module runner ---
header('Location: module-advanced.php');
exit;