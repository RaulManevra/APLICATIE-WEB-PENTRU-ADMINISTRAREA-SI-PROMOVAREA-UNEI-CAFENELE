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
$stmt = $conn->prepare("SELECT id, email, username, password, role, PuncteFidelitate, PPicture FROM users WHERE email = ? OR username = ? LIMIT 1");
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

// Handle Remember Me
if (isset($_POST['remember_me'])) {
    $selector = bin2hex(random_bytes(16)); // Public ID
    $validator = bin2hex(random_bytes(32)); // Secret to hash
    $hashedValidator = hash('sha256', $validator);
    $expiry = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days

    $insert = $conn->prepare("INSERT INTO user_tokens (selector, validator, user_id, expires_at) VALUES (?, ?, ?, ?)");
    $insert->bind_param("ssis", $selector, $hashedValidator, $user['id'], $expiry);
    $insert->execute();

    // Set cookie: selector:validator
    // Note: We are setting it for 30 days. Secure flag depends on HTTPS.
    $cookieValue = "$selector:$validator";
    setcookie('remember_me', $cookieValue, [
        'expires' => time() + (30 * 24 * 60 * 60),
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax', // or Strict
        // 'secure' => ... (handled by environment usually, but good to be explicit if on HTTPS)
    ]);
}

sendSuccess(['redirect' => 'home']);
$conn->close();
exit;
