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

/* ============================================================
   ADVANCED MODULE – 15 QUESTIONS (English)
   ------------------------------------------------------------
   Supported types:
     • radio    (one correct)
     • checkbox (multiple correct)
     • rank     (dropdown 1-n order)
   ============================================================ */
return [

/* 1 — checkbox (image) */
[
    'type'     => 'checkbox',
    'question' => 'E-mail signed by the CFO (see capture) – tick EVERY subtle red flag.',
    'image'    => 'img/adv_q1.png',
    'options'  => [
        'Signature links to an external HTTP URL',
        'Date format is US, not EU',
        'Text is flawless – no mistakes',
        'Encrypted attachment with the password in a separate e-mail'
    ],
    'answer'   => [0, 1]
],

/* 2 — rank (suspicion order) */
[
    'type'     => 'rank',
    'question' => 'Rank these three messages from MOST to LEAST suspicious.',
    'options'  => [
        'Teams: “Ping me ASAP” with a shortened link',
        'SMS: “Your parcel arrives today” from an unknown number',
        'E-mail: “FYI – meeting minutes” with no links'
    ],
    'answer'   => [0, 1, 2]
],

/* 3 — radio (image) */
[
    'type'     => 'radio',
    'question' => 'In this WhatsApp Web preview, what exposes it as fake?',
    'image'    => 'img/adv_q3.png',
    'options'  => [
        'Time is shown in 24-hour format',
        'Preview-card domain does not match the text',
        'Uses emoji in the message'
    ],
    'answer'   => 1
],

/* 4 — checkbox */
[
    'type'     => 'checkbox',
    'question' => 'Calendar invite “Zoom Payroll Review”. Select EVERY problem.',
    'options'  => [
        'Organiser is external to the company',
        '.ics file attached',
        'Unusual time (23:00)',
        'Personalised greeting with your name'
    ],
    'answer'   => [0, 1, 2]
],

/* 5 — checkbox (image) */
[
    'type'     => 'checkbox',
    'question' => 'Authenticator push capture – tick the alerts.',
    'image'    => 'img/adv_q5.png',
    'options'  => [
        'App label “Outlook (Legacy)” in 2025',
        'Request comes from an unexpected country',
        'Exact time matches your recent login',
        'Official Microsoft logo'
    ],
    'answer'   => [0, 1]
],

/* 6 — radio */
[
    'type'     => 'radio',
    'question' => 'A 2FA SMS arrives from a new short code. What does this imply?',
    'options'  => [
        'New number = likely phishing',
        'Bank rotates numbers every week',
        'Only changes for premium customers'
    ],
    'answer'   => 0
],

/* 7 — checkbox (image) */
[
    'type'     => 'checkbox',
    'question' => 'Scan this “Free Wi-Fi” QR code. Which signs are suspicious?',
    'image'    => 'img/adv_q7.png',
    'options'  => [
        'Redirects to an external domain',
        'Shortened URL',
        'Displayed on an official company poster',
        'No password required'
    ],
    'answer'   => [0, 1, 3]
],

/* 8 — rank (urgency) */
[
    'type'     => 'rank',
    'question' => 'Rank these subjects by urgency (1 = most urgent).',
    'options'  => [
        'Final notice: action required',
        'Action required',
        'FYI: quarterly report'
    ],
    'answer'   => [0, 1, 2]
],

/* 9 — checkbox */
[
    'type'     => 'checkbox',
    'question' => '“e-Fax received” e-mail. Which elements are risky?',
    'options'  => [
        'Password-protected PDF in the same e-mail',
        'Valid HTTPS link',
        'Internal sender address “fax@corp.local”',
        'Self-extracting ZIP attachment'
    ],
    'answer'   => [0, 3]
],

/* 10 — radio (image) */
[
    'type'     => 'radio',
    'question' => '“Dream job” offer on LinkedIn (see capture). What makes it suspicious?',
    'image'    => 'img/adv_q10.png',
    'options'  => [
        '0 mutual connections and stock photo',
        'Very short message',
        'Competitive salary offered'
    ],
    'answer'   => 0
],

/* 11 — checkbox */
[
    'type'     => 'checkbox',
    'question' => 'Apple Wallet push “boarding pass update”. Tick EVERYTHING unusual.',
    'options'  => [
        'Domain “fly-updates.co” (not the airline)',
        'Arrives hours after last real change',
        'Uses the airline’s official logo',
        'Does not ask the user to act'
    ],
    'answer'   => [0, 1]
],

/* 12 — rank (header image) */
[
    'type'     => 'rank',
    'question' => 'Order these three Received headers along the REAL route (1 = first hop).',
    'image'    => 'img/adv_q12.png',
    'options'  => [
        'Internal host 10.0.5.12',
        'smtp-external.mail.xyz',
        'mx-corp.local'
    ],
    'answer'   => [1, 2, 0]
],

/* 13 — checkbox */
[
    'type'     => 'checkbox',
    'question' => 'Teams message from a “colleague”. What exposes the fake?',
    'options'  => [
        'Missing “External” banner',
        'Correct avatar but not in directory',
        'Uses unusual informal greetings',
        'Includes link to internal SharePoint'
    ],
    'answer'   => [0, 1, 2]
],

/* 14 — radio */
[
    'type'     => 'radio',
    'question' => 'Call from “IT Desk” using 09… Why be suspicious?',
    'options'  => [
        '09 prefix is a personal mobile',
        'They speak fluent English',
        'Call lasts only 15 s'
    ],
    'answer'   => 0
],

/* 15 — boolean (radio) */
[
    'type'     => 'radio',
    'question' => 'A link with https:// is always safe.',
    'options'  => ['True', 'False'],
    'answer'   => 1
],

];
