<?php
session_start();
header('Content-Type: application/json'); // we return JSON

require_once __DIR__ . '/db_config.php';

// Helper function for errors
function sendError($msg) {
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError("Invalid request method.");
}

// Get POST data
$email = trim($_POST['email'] ?? '');
$psw = $_POST['psw'] ?? '';
$psw_repeat = $_POST['psw-repeat'] ?? '';

// Basic validation
if (empty($email) || empty($psw) || empty($psw_repeat)) {
    sendError("All fields are required.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError("Invalid email address.");
}
if ($psw !== $psw_repeat) {
    sendError("Passwords do not match.");
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    sendError("Email already registered.");
}
$stmt->close();

// Hash password
$hashedPassword = password_hash($psw, PASSWORD_DEFAULT);

// Insert user
$stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'user')");
$stmt->bind_param("ss", $email, $hashedPassword);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'user';
    echo json_encode(['success' => true, 'redirect' => 'home']);
} else {
    sendError("Registration failed. Try again.");
}

$stmt->close();
$conn->close();
