<?php
require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/../core/output.php';
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

// Handle Remember Me Destruction
if (isset($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me']);
    if (count($parts) === 2) {
        list($selector, $validator) = $parts;
        // Delete specific token
        $stmt = $conn->prepare("DELETE FROM user_tokens WHERE selector = ?");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
    }
    // Delete cookie
    setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER["HTTPS"]), true);
    unset($_COOKIE['remember_me']);
}

SessionManager::logout();

sendSuccess(['redirect' => 'home']);
exit;
