<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../core/output.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError("Invalid request method.");
}

if (!verify_csrf()) {
    sendError("Invalid session token (CSRF). Refresh page and try again.");
}

$uname = trim($_POST['uname'] ?? '');
$psw = $_POST['psw'] ?? '';

if ($uname === '' || $psw === '') {
    sendError("All fields are required.");
}

// Find user by email or username using prepared statement
$stmt = $conn->prepare("SELECT id, email, username, password, role FROM users WHERE email = ? OR username = ? LIMIT 1");
$stmt->bind_param("ss", $uname, $uname);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    sendError("User not found.");
}

if (!password_verify($psw, $user['password'])) {
    sendError("Incorrect password.");
}

// Successful login
SessionManager::login($user);

sendSuccess(['redirect' => 'home']);
$conn->close();
exit;
