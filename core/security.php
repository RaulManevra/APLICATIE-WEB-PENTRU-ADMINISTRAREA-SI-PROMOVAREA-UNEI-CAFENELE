<?php
// Centralized security settings — require this as the first include in handlers/pages.

declare(strict_types=1);

// Enforce HTTPS by header (proper enforcement via server config is recommended)
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
    header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
}

// Common security headers
header('X-Frame-Options: DENY');
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Permissions-Policy: geolocation=(), microphone=()");
header("X-XSS-Protection: 1; mode=block");

// Basic CSP — adjust if you use external CDNs
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://*.googleusercontent.com https://*.google.com;");

require_once __DIR__ . '/SessionManager.php';

// Session cookie params and start session
SessionManager::start();

// Regenerate CSRF token if missing (csrf.php also does this, but safe to ensure)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
