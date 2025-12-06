<?php
require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/../core/output.php';
header('Content-Type: application/json; charset=utf-8');

SessionManager::logout();

sendSuccess(['redirect' => 'home']);
exit;
