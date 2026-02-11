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

$basic_questions = [

    // Question 1 – domain clue
    [
        'type'     => 'radio',
        'question' => 'The sender is bancofscotlandd@banc.co.uk. What is the clearest phishing clue?',
        'options'  => [
            'Misspelled subject line',
            'Suspicious sender domain',
            'Correct corporate signature'
        ],
        'answer'   => 1
    ],

    // Question 2 – prize email
    [
        'type'     => 'radio',
        'question' => 'You received an email from giveaway@amaz0n-prize.com offering a £500 coupon. What is the safest action?',
        'options'  => [
            'Click to claim',
            'Mark it as spam / phishing',
            'Forward to a friend to check'
        ],
        'answer'   => 1
    ],

    // Question 3 – verifying emails
    [
        'type'     => 'radio',
        'question' => 'Which of the following is a good way to verify a suspicious email?',
        'options'  => [
            'Reply to the sender',
            'Call the company using official contact details',
            'Click the link to verify'
        ],
        'answer'   => 1
    ],

    // Question 4 – Amazon phishing clues (image)
    [
        'type'     => 'checkbox',
        'question' => 'Identify signs of phishing in this Amazon email.',
        'image'    => 'img/basic_q4.png',
        'options'  => [
            'Use of "lock" instead of "locked"',
            'Spelling and grammar mistakes',
            'Generic greeting like "Hi Dear"',
            'Legitimate Amazon logo',
            'The Amazon time signature',
            'Proper formatting and language'
        ],
        'answer'   => [0, 1, 2, 4]
    ],

    // Question 5 – Outlook phishing (image)
    [
        'type'     => 'checkbox',
        'question' => 'What is the problem with this Outlook email?',
        'image'    => 'basic_q1.webp',
        'options'  => [
            'Urgency to reactivate account',
            'Incorrect domain',
            'Professional grammar throughout',
            'No salutation',
            'Broken link visible'
        ],
        'answer'   => [0, 1, 3]
    ],

    // Question 6 – PayPal phishing (image)
    [
        'type'     => 'checkbox',
        'question' => 'Which parts of this PayPal email indicate phishing?',
        'image'    => 'basic_q2.webp',
        'options'  => [
            'Spelling errors',
            'Overuse of urgency',
            'No contact info',
            'Unprofessional formatting',
            'Official PayPal language',
            'Generic greeting'
        ],
        'answer'   => [0, 1, 3, 5]
    ],

    // Question 7 – HR email scam (image)
    [
        'type'     => 'checkbox',
        'question' => 'What makes this salary notification email suspicious?',
        'image'    => 'basic_q3.webp',
        'options'  => [
            'No recipient name',
            'Link instead of attachment',
            'Unexpected raise claim',
            'Perfect formatting',
            'No contact details'
        ],
        'answer'   => [0, 1, 2, 4]
    ],

    // Question 8 – Domain trick
    [
        'type'     => 'radio',
        'question' => 'Which domain seems suspicious for an email from your bank?',
        'options'  => [
            'support@yourbank-secure.com',
            'support@yourbank.com',
            'security@bank.com'
        ],
        'answer'   => 0
    ],

    // Question 9 – Phishing signs
    [
        'type'     => 'radio',
        'question' => 'What is a typical sign of a phishing email?',
        'options'  => [
            'Use of personal names',
            'Unexpected attachment',
            'Link to verify credentials'
        ],
        'answer'   => 2
    ],

    // Question 10 – Urgent password request
    [
        'type'     => 'radio',
        'question' => 'You receive an email asking for your password urgently. What should you do?',
        'options'  => [
            'Reply with the password',
            'Click the link immediately',
            'Report as phishing'
        ],
        'answer'   => 2
    ]

];


/* Store in session and redirect */
session_start();
$_SESSION['questions']        = $basic_questions;
$_SESSION['current_question'] = 0;
$_SESSION['answers']          = [];
header('Location: module-basic.php');
exit;
