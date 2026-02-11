<?php
/**
 * /php/login.php — Secure login processor (PDO + CSRF + rate limiting)
 * 
 * Responsibilities:
 * - Accept POSTed login credentials
 * - Validate CSRF token and basic input
 * - Enforce simple rate limiting (per session)
 * - Verify user (constant-time password check via password_verify)
 * - Start authenticated session (session_regenerate_id)
 * - Redirect on success/failure with generic error flag
 * 
 * Security highlights:
 * - No raw session_start(); session & error policy handled in config.php
 * - No echo/print before headers (avoid "headers already sent")
 * - Generic errors (do not reveal whether email exists)
 * - Uses prepared statements (PDO) to prevent SQLi
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/security.php';
require_once __DIR__ . '/../lib/csrf.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/auth.php';

// Send security headers (even if this page is mostly server-side)
send_security_headers();

// Convenience: target locations (adjust if you move files)
$LOGIN_PAGE     = app_url('login.php');       // visible form
$DASHBOARD_PAGE = app_url('dashboard.php');   // post-login landing

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Reject non-POST quietly
    header('Location: ' . $LOGIN_PAGE . '?error=1');
    exit;
}

// Enforce CSRF protection
$token = $_POST['csrf_token'] ?? null;
if (!csrf_validate($token)) {
    // Invalid/missing token -> generic error
    header('Location: ' . $LOGIN_PAGE . '?error=1');
    exit;
}

// Enforce simple rate limiting (per session)
if (login_rate_limited()) {
    header('Location: ' . $LOGIN_PAGE . '?error=1');
    exit;
}

// Collect & normalize input
$emailRaw = trim((string)($_POST['email'] ?? ''));
$passRaw  = (string)($_POST['password'] ?? '');

// Basic validation
if ($emailRaw === '' || $passRaw === '' || !filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
    note_login_attempt();
    header('Location: ' . $LOGIN_PAGE . '?error=1');
    exit;
}

// Normalize email
$email = mb_strtolower($emailRaw);

// Look up user with PDO prepared statement
try {
    $sql = 'SELECT id, username, email, password_hash FROM users WHERE email = ? LIMIT 1';
    $rows = db_query($sql, [$email]);
} catch (Throwable $e) {
    header('Location: ' . $LOGIN_PAGE . '?error=1');
    exit;
}

// Verify user & password (generic outcome on failure)
if (!$rows || !is_array($rows) || count($rows) !== 1) {
    note_login_attempt();
    header('Location: ' . $LOGIN_PAGE . '?error=1');
    exit;
}

$user = $rows[0];

// Verify password using constant-time function
if (!password_verify_secure($passRaw, $user['password_hash'])) {
    note_login_attempt();
    header('Location: ' . $LOGIN_PAGE . '?error=1');
    exit;
}

// Success: rotate session ID and store minimal auth state
start_user_session($user);
reset_login_attempts();

// Optional: additional post-auth checks (e.g., force password change flags)

// Redirect to dashboard
header('Location: ' . $DASHBOARD_PAGE);
exit;