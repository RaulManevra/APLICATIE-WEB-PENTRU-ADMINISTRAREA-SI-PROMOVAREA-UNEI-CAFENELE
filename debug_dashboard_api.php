<?php
// Mock Environment for CLI
$_SERVER['SERVER_PORT'] = 80;
$_SERVER['HTTPS'] = 'off';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'DebugScript';

// Mock Session
require_once __DIR__ . '/core/SessionManager.php';
// Mock Active Session
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1; 
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'AdminDebug';
$_SESSION['logged_in'] = true; 

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/DashboardController.php';

// Capture output
ob_start();
$controller = new DashboardController($conn);
$_GET['action'] = 'get_dashboard_stats';

try {
    $controller->handleRequest();
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
$output = ob_get_clean();
echo "Raw Output:\n" . $output . "\n";

// Decode to verify
$json = json_decode($output, true);
if ($json) {
    echo "Decoded Stats:\n";
    print_r($json['data']['stats']);
} else {
    echo "Failed to decode JSON.\n";
}
