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
   INTERMEDIATE MODULE – 15 QUESTIONS (radio / checkbox only)
   ============================================================ */
$inter_questions = [

 /* 1 ─ radio */
 [ 'type'=>'radio',
   'question'=>'Which sender domain is MOST suspicious for Microsoft 365 support?',
   'options'=>[
       'support@microsoft365.com',
       'security@ms365-verify.com',
       'noreply@admin.microsoft.com'
   ],
   'answer'=>1 ],

 /* 2 ─ radio */
 [ 'type'=>'radio',
   'question'=>'“Payment declined – update card at pay-safe-stripe.com”.  Safe action?',
   'options'=>[
       'Click the link and update card',
       'Ignore and log in via stripe.com directly',
       'Forward to finance for checking'
   ],
   'answer'=>1 ],

 /* 3 ─ checkbox */
 [ 'type'=>'checkbox',
   'question'=>'Tick every indicator of spear-phishing.',
   'options'=>[
       'E-mail knows your last invoice number',
       'Generic “Dear Customer” salutation',
       'Mentions your colleague by name',
       'Sent to info@ mailing list'
   ],
   'answer'=>[0,2] ],

 /* 4 ─ checkbox  (NUEVA – llamada entrante) */
 [ 'type'=>'checkbox',
 'question'=>'Look at the incoming call screenshot and select EVERY red flag.',
 'image'=>'assets/img/intermediate_q4.png',   // ← nombre real
 'options'=>[
     'Caller ID shows only “MOM” (easily spoofable)',
     'Number uses +1-809 premium-rate code',
     'Local time is midday',
     'Claims “urgent—call back immediately” in subtitle'
 ],
 'answer'=>[0,1,3] ],


 /* 5 ─ radio */
 [ 'type'=>'radio',
   'question'=>'What does DMARC primarily help prevent?',
   'options'=>[
       'Password spraying',
       'Domain spoofing in e-mail',
       'Malicious macros'
   ],
   'answer'=>1 ],

 /* 6 ─ checkbox */
 [ 'type'=>'checkbox',
   'question'=>'You open an HTML e-mail and see `<iframe width=\"0\" height=\"0\">`.  Select all valid concerns.',
   'options'=>[
       'Tracking beacon to detect opens',
       'Optimises the layout for mobiles',
       'May load malicious code silently',
       'Improves image resolution'
   ],
   'answer'=>[0,2] ],

 /* 7 ─ radio */
 [ 'type'=>'radio',
   'question'=>'A message claims: “We detected login from Nigeria. Click here to secure account.”  Biggest red-flag?',
   'options'=>[
       'Location mentioned',
       'Urgent call to action via link',
       'Uses your full name'
   ],
   'answer'=>1 ],

  /* 8 ─ checkbox  (NUEVA – SMS de “DAD”) */
  [ 'type'=>'checkbox',
  'question'=>'Review this SMS from “DAD” and tick EACH sign it’s a scam.',
  'image'=>'assets/img/intermediate_q8.png',
  'options'=>[
      'Message asks to send money to a third-party account',
      'Includes a shortened link',
      'Spelling / case inconsistent with real parent',
      'Says “phone about to die” to rush you'
  ],
  'answer'=>[0,1,3] ],

 /* 9 ─ radio */
 [ 'type'=>'radio',
   'question'=>'Homoglyph “аpple.com” with Cyrillic “а” belongs to which technique?',
   'options'=>['Typosquatting','Homograph spoofing','Subdomain takeover'],
   'answer'=>1 ],

 /*10 ─ checkbox */
 [ 'type'=>'checkbox',
   'question'=>'Select ALL headers an attacker is most likely to forge.',
   'options'=>['From:','Date:','Reply-To:','Message-ID:'],
   'answer'=>[0,2] ],

 /*11 ─ radio */
 [ 'type'=>'radio',
   'question'=>'An e-mail attachment ends with “.js”.  Is it directly executable when opened?',
   'options'=>['Yes – JavaScript runs','No – Only in browsers','Only on Linux'],
   'answer'=>0 ],

 /*12 ─ checkbox  (NUEVA – login bancario falso) */
  [ 'type'=>'checkbox',
   'question'=>'Identify ALL visual clues that this bank login page is fake.',
   'image'=>'assets/img/intermediate_q12.png',
   'options'=>[
       'URL bar shows “http://” not “https://”',
       'Domain contains extra word e.g. secure-bank-login.com',
       'Outdated bank logo',
       'Typos in help text'
   ],
   'answer'=>[0,1,2,3] ],
 /*13 ─ radio */
 [ 'type'=>'radio',
   'question'=>'Your company uses SSO; an e-mail asks to “re-enter your AD credentials”.  Best response?',
   'options'=>['Comply – normal routine','Report as phishing','Ignore forever'],
   'answer'=>1 ],

 /*14 ─ checkbox */
 [ 'type'=>'checkbox',
   'question'=>'In a fake Microsoft login page, what visual clues expose it? (Select all)',
   'options'=>[
       'URL bar shows “http://”',
       'Old Microsoft logo',
       'Perfect grammar and branding',
       'Typos in help text'
   ],
   'answer'=>[0,1,3] ],

 /*15 ─ radio */
 [ 'type'=>'radio',
   'question'=>'True or False:  Opening unknown links only inside a VM sandbox eliminates phishing risk.',
   'options'=>['True','False'],
   'answer'=>1 ],
];

/* ---- session & redirect ---- */
session_start();
$_SESSION['inter_questions']  = $inter_questions;
$_SESSION['inter_idx']        = 0;
$_SESSION['inter_ans']        = array_fill(0, count($inter_questions), null);
header('Location: module-intermediate.php');
exit;
?>