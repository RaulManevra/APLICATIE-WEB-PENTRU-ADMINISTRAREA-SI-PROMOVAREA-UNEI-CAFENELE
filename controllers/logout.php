<?php
// include/logout.php
require_once __DIR__ . '/../core/security.php';
header('Content-Type: application/json; charset=utf-8');

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

echo json_encode(['success' => true, 'redirect' => 'home'], JSON_UNESCAPED_UNICODE);
exit;
