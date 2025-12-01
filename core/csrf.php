<?php
declare(strict_types=1);

require_once __DIR__ . '/security.php'; // ensures session started and token exists

function csrf_token(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token']);
    }
    return false;
}
