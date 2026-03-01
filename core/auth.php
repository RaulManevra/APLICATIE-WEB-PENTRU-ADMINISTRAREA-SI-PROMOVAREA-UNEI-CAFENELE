<?php
require_once __DIR__ . '/SessionManager.php';

function require_login() {
    if (!SessionManager::isLoggedIn()) {
        http_response_code(401);
        exit("Unauthorized");
    }
}

function require_role(array $allowedRoles) {
    require_login();
    $userData = SessionManager::getCurrentUserData();
    $userRoles = $userData['roles'] ?? [];

    // Check if user has at least one of the allowed roles
    $hasAccess = false;
    foreach ($allowedRoles as $role) {
        if (in_array($role, $userRoles)) {
            $hasAccess = true;
            break;
        }
    }

    if (!$hasAccess) {
        http_response_code(403);
        // Debug info for pair programming context
        exit("Forbidden. Required role not found. Your roles: " . json_encode($userRoles));
    }
}

function require_admin() {
    require_role(['admin']);
}
