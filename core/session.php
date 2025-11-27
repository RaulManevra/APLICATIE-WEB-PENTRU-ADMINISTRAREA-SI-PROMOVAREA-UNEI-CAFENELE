<?php
declare(strict_types=1);
require_once __DIR__ . '/security.php'; // ensures session is started and secure cookie params set

header('Content-Type: application/json; charset=utf-8');

$username = SessionManager::getCurrentUser();

echo json_encode([
    'username' => $username
], JSON_UNESCAPED_UNICODE);
exit;
