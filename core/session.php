<?php
declare(strict_types=1);
require_once __DIR__ . '/security.php'; // ensures session is started and secure cookie params set

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$username = SessionManager::getCurrentUser();

echo json_encode([
    'username' => $username,
    'roles' => SessionManager::getCurrentUserRoles()
], JSON_UNESCAPED_UNICODE);
exit;
