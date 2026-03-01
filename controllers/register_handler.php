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

// Retrieve and validate
// NEW: Retrieve username as well
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$psw = $_POST['psw'] ?? '';
$psw_repeat = $_POST['psw-repeat'] ?? '';

if ($username === '' || $email === '' || $psw === '' || $psw_repeat === '') {
    sendError("All fields are required.");
}

// NEW: Validate username format (same regex as in HTML)
if (!preg_match('/^[a-z_.]{3,15}$/', $username)) {
    sendError("Username invalid. Must be 3-15 lowercase characters (letters, dots, underscores).");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError("Invalid email address.");
}
if ($psw !== $psw_repeat) {
    sendError("Passwords do not match.");
}

// Check existing email OR username
// NEW: Check if email OR username already exists
$stmt = $conn->prepare("SELECT id, email, username FROM users WHERE email = ? OR username = ?");
$stmt->bind_param("ss", $email, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Check which one already exists to give a clear message
    if ($row['email'] === $email) {
        $stmt->close();
        sendError("Email already registered.");
    }
    if ($row['username'] === $username) {
        $stmt->close();
        sendError("Username already taken.");
    }
}
$stmt->close();

// Hash password
$options = ['cost' => 12];
$hashedPassword = password_hash($psw, PASSWORD_BCRYPT, $options);

// Insert user
// NEW: Insert username into database
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role, PuncteFidelitate, PPicture) VALUES (?, ?, ?, 'user', 0, 'assets/public/default.png')");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

if ($stmt->execute()) {
    // Construct user array for SessionManager
    $user = [
        'id' => $stmt->insert_id,
        'email' => $email,
        'username' => $username,
        'role' => 'user',
        'PuncteFidelitate' => 0,
        'PPicture' => 'assets/public/default.png'
    ];
    
    SessionManager::login($user);

    sendSuccess(['redirect' => 'home']);
    $stmt->close();
    $conn->close();
    exit;
} else {
    $stmt->close();
    $conn->close();
    sendError("Registration failed.");
}