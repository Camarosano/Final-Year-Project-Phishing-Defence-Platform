<?php
/**
 * Quiz reset script (secure version)
 * - Clears previous quiz session state
 * - Redirects back to questions.php to load a fresh set
 *
 * Security notes:
 * - Do not use raw session_start(); session is already configured in config.php
 * - Avoid leaking output before header()
 * - Unset only the relevant keys to prevent side effects
 */

// === Global security and utility includes ===
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/lib/security.php';

// Send security headers (harmless here, consistent across all pages)
send_security_headers();

// --- Reset only the quiz session keys ---
unset(
    $_SESSION['questions'],
    $_SESSION['answers'],
    $_SESSION['current_question']
);

// --- Redirect to the question loader ---
header('Location: questions.php');
exit;