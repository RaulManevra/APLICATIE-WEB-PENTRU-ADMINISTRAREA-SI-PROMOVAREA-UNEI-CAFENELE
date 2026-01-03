<?php
require_once __DIR__ . '/../core/SessionManager.php';
header('Content-Type: application/json');
echo json_encode(['loggedIn' => SessionManager::isLoggedIn()]);
?>
