<?php
require_once __DIR__ . '/SessionManager.php';

function require_login() {
    if (!SessionManager::isLoggedIn()) {
        http_response_code(401);
        exit("Unauthorized");
    }
}

function require_admin() {
    require_login();
    $userData = SessionManager::getCurrentUserData();
    $roles = $userData['roles'] ?? [];

    if (!in_array("admin", $roles)) {
        http_response_code(403);
        exit("Forbidden");
    }
}
