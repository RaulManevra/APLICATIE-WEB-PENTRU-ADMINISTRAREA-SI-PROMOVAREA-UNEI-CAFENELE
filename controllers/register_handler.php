<?php
// include/register_handler.php
declare(strict_types=1);
require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../core/output.php';

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
// NOU: Preluam si username-ul
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$psw = $_POST['psw'] ?? '';
$psw_repeat = $_POST['psw-repeat'] ?? '';

if ($username === '' || $email === '' || $psw === '' || $psw_repeat === '') {
    sendError("All fields are required.");
}

// NOU: Validam formatul username-ului (acelasi regex ca in HTML)
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
// NOU: Verificam daca exista deja email-ul SAU username-ul
$stmt = $conn->prepare("SELECT id, email, username FROM users WHERE email = ? OR username = ?");
$stmt->bind_param("ss", $email, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Verificam care dintre ele exista deja pentru a da un mesaj clar
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
// NOU: Introducem si username-ul in baza de date
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

if ($stmt->execute()) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['email'] = $email;
    // NOU: Salvam username-ul in sesiune
    $_SESSION['username'] = $username;
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