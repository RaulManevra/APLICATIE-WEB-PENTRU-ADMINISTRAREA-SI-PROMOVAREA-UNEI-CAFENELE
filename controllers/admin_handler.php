<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/output.php';
require_once __DIR__ . '/ProductController.php';

// Ensure strictly JSON response
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0'); // Suppress HTML errors
error_reporting(E_ALL); // Log errors instead

// ====================================
// CSRF Check (Assuming verify_csrf is available from security.php or csrf.php)
require_once __DIR__ . '/../core/csrf.php';
if (!verify_csrf()) {
   // For GET requests sometimes we might skip, but for admin actions strictly enforce
   // If it's a simple Fetch GET, we usually pass token in header.
   // For now, let's assume all admin actions need CSRF or we can loosen for GET if needed.
   // Actually, standard practice for API:
   if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
       // Only enforce on state-changing methods
        if (!verify_csrf()) {
             sendError("Invalid session token.");
        }
   }
}

// ====================================
// Auth Check
// require_admin() usually checks Auth and redirects or exits.
// Since this is an AJAX handler, we want JSON error, not HTML redirect.
if (!SessionManager::isLoggedIn()) {
    http_response_code(401);
    sendError("Unauthorized. Please login.");
}

$userData = SessionManager::getCurrentUserData();
$roles = $userData['roles'] ?? [];
if (!in_array('admin', $roles)) {
    http_response_code(403);
    sendError("Forbidden. Admin access required.");
}

// ====================================
// Route to Controller
$entity = $_POST['entity'] ?? $_GET['entity'] ?? 'product';

if ($entity === 'slider') {
    require_once __DIR__ . '/SliderController.php';
    $controller = new SliderController($conn);
} elseif ($entity === 'table') {
    require_once __DIR__ . '/TableController.php';
    $controller = new TableController($conn);
} elseif ($entity === 'reservation') {
    require_once __DIR__ . '/ReservationController.php';
    $controller = new ReservationController($conn);
} elseif ($entity === 'dashboard' || $entity === 'settings') {
    require_once __DIR__ . '/DashboardController.php';
    $controller = new DashboardController($conn);
} elseif ($entity === 'user') {
    require_once __DIR__ . '/UserController.php';
    $controller = new UserController($conn);
} elseif ($entity === 'order') {
    require_once __DIR__ . '/OrderController.php';
    $controller = new OrderController($conn);
} else {
    $controller = new ProductController($conn);
}

$controller->handleRequest();

$conn->close();
