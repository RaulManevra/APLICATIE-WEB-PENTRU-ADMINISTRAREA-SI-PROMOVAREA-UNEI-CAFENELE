<?php
// include/register_handler.php
declare(strict_types=1);
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/output.php';

header('Content-Type: application/json; charset=utf-8');

function sendError(string $msg) {
    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError("Invalid request method.");
}

if (!verify_csrf()) {
    sendError("Invalid session token (CSRF). Refresh page and try again.");
}

// Retrieve and validate
$email = trim($_POST['email'] ?? '');
$psw = $_POST['psw'] ?? '';
$psw_repeat = $_POST['psw-repeat'] ?? '';

if ($email === '' || $psw === '' || $psw_repeat === '') {
    sendError("All fields are required.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError("Invalid email address.");
}
if ($psw !== $psw_repeat) {
    sendError("Passwords do not match.");
}

// Check existing email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    sendError("Email already registered.");
}
$stmt->close();

// Hash password using bcrypt (explicit)
$options = ['cost' => 12];
$hashedPassword = password_hash($psw, PASSWORD_BCRYPT, $options);

// Insert user (use prepared statement)
$stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'user')");
$stmt->bind_param("ss", $email, $hashedPassword);

if ($stmt->execute()) {
    // Secure session handling
    session_regenerate_id(true);
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'user';

    echo json_encode(['success' => true, 'redirect' => 'home'], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $conn->close();
    exit;
} else {
    $stmt->close();
    $conn->close();
    sendError("Registration failed.");
}
