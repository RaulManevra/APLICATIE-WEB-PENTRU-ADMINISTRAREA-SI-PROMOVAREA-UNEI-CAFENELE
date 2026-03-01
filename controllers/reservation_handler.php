<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/output.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/ReservationController.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError("Invalid request method.");
}

// 1. CSRF Check
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        sendError("Invalid session token.");
    }
}

// 2. Controller
$controller = new ReservationController($conn);
$controller->handleRequest();

$conn->close();
?>
