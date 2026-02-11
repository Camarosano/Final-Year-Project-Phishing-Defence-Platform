# Phishing Defence Platform  
*A Web-Based Phishing Awareness & Training System*

Final Year Project – BSc (Hons) Cybersecurity (R60)  
The Open University  

---

## Overview

The **Phishing Defence Platform** is a security-focused web application designed to train users to recognise and resist phishing attacks through interactive, progressive learning modules.

Phishing remains one of the most reported cyber threats affecting organisations and individuals. This project responds to that risk by combining:

- Behavioural security training
- Progressive difficulty modules
- Secure web application architecture
- Defensive security engineering practices

The platform was built using:

- **PHP**
- **HTML / CSS / JavaScript**
- **MariaDB (MySQL)**
- **XAMPP (Apache + MariaDB)**

---

## Core Features

### User System
- Secure user registration & login
- Password hashing (`password_hash()` / `password_verify()`)
- Session ID regeneration on login/logout
- Secure logout flow
- Token-based password recovery (single-use, hashed, expiring tokens)

---

### Training Modules

Three progressive difficulty levels:

1. **Basic** – obvious phishing indicators  
2. **Intermediate** – more subtle red flags  
3. **Advanced** – realistic spear-phishing scenarios  

Each module includes:

- Multiple-choice questions
- Immediate feedback
- Scoring system (+1 / 0 / -0.25)
- Review screen
- Performance tracking

---

### Dashboard & Performance

- User score tracking
- Chart.js performance graphs
- Historical attempt storage
- Cybersecurity news integration (contextual awareness)

---

## Security Engineering (Hardening Phase)

The project evolved from an initial functional prototype into a hardened web application during the final stage.

### Implemented Security Controls

### CSRF Protection
- Per-form synchroniser tokens
- Server-side validation
- Token regeneration on critical events

### Session & Cookie Hardening
- `Secure` flag
- `HttpOnly` flag
- `SameSite` policy
- Session ID regeneration on privilege changes

### Clickjacking Protection
- Content Security Policy (CSP)
- `frame-ancestors 'none'`

### Database Security
- PDO prepared statements
- Parameter binding
- No direct SQL concatenation

### Password Recovery Security
- Cryptographically secure random tokens
- Hashed token storage
- Short expiry time
- Single-use enforcement
- Generic responses to prevent account enumeration

---

## Security Testing

The platform was tested using:

- **OWASP ZAP** (passive & active scanning)
- **Burp Suite** (manual request inspection)
- Browser DevTools (header & cookie verification)

Issues identified during earlier development stages were resolved in the final hardened version.

---

## Architecture Overview

The application follows a modular structure:

config/
lib/
├── security.php
├── csrf.php
├── auth.php
└── db.php


All protected pages load shared security components to enforce:

- Secure headers
- Session policy
- CSRF validation
- Authentication checks

This design supports maintainability and scalability.

---

## Database Structure

Minimal data retention model:

- `users`
- `quiz_questions`
- `quiz_results`
- `password_resets`

Sensitive data is minimised and secured.

---

## Accessibility Considerations

The platform considers:

- WCAG 2.2 principles
- Clear language design
- Compatibility with browser accessibility tools
- Multilingual testing

---

## Future Improvements

- Question randomisation
- Timed challenge mode
- Expanded scenario library
- Production deployment
- Enhanced reporting dashboard
- Further OWASP-based security testing

---

## Academic & Professional Foundations

The design was informed by:

- OWASP Cheat Sheets
- ENISA Threat Landscape reports
- NIST SP 800-63B guidance
- ISO/IEC 27001:2022 principles
- CyBOK Human Factors knowledge area

---

## Purpose

This project demonstrates:

- Secure web application development
- Threat-informed design
- Defensive security implementation
- Risk-based security decision-making
- Practical application of cybersecurity theory

---

## Author

Bruno A. Camarosano Soto  
BSc (Hons) Cybersecurity – The Open University
