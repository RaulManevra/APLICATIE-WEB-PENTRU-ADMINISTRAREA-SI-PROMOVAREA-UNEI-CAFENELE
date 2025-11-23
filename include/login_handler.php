<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db_config.php';

function sendError($msg) {
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError("Invalid request method.");
}

$uname = trim($_POST['uname'] ?? '');
$psw = $_POST['psw'] ?? '';

if (empty($uname) || empty($psw)) {
    sendError("All fields are required.");
}

// Try to find user by email or username
$stmt = $conn->prepare("SELECT id, email, username, password, role FROM users WHERE email = ? OR username = ? LIMIT 1");
$stmt->bind_param("ss", $uname, $uname);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    sendError("User not found.");
}

if (!password_verify($psw, $user['password'])) {
    sendError("Incorrect password.");
}

// Login successful
$_SESSION['user_id'] = $user['id'];
$_SESSION['email'] = $user['email'];
$_SESSION['username'] = $user['username'] ?? '';
$_SESSION['role'] = $user['role'];

echo json_encode(['success' => true, 'redirect' => 'home']);

$stmt->close();
$conn->close();
