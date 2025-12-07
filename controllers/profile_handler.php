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

if (!SessionManager::isLoggedIn()) {
    sendError("You must be logged in to upload a profile picture.");
}

if (!isset($_FILES['profile-picture'])) {
    sendError("No file uploaded.");
}

$file = $_FILES['profile-picture'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    switch ($file['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            sendError("File is too large.");
            break;
        case UPLOAD_ERR_NO_FILE:
            sendError("No file was uploaded.");
            break;
        default:
            sendError("File upload error.");
    }
}

// Validate file size (e.g., max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    sendError("File size exceeds 5MB limit.");
}

// Validate MIME type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

if (!in_array($mimeType, $allowedMimeTypes)) {
    sendError("Invalid file type. Only JPG, PNG, and GIF are allowed.");
}

// Get current user ID
$userId = $_SESSION['user_id'];

// Get old profile picture to delete
$stmt = $conn->prepare("SELECT PPicture FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$oldPicture = null;
if ($row = $result->fetch_assoc()) {
    $oldPicture = $row['PPicture'];
}
$stmt->close();

// Prepare upload directory
$uploadDir = __DIR__ . '/../assets/uploads/profile_pictures/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        sendError("Failed to create upload directory.");
    }
}

// Generate new filename: PP_userid_ID.extension
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
// Sanitize extension just in case
$extension = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $extension));
$filename = 'PP_userid_' . $userId . '.' . $extension;
$destination = $uploadDir . $filename;

// Delete old file if it exists and is not default
// Note: We check if strict file exists to avoid error suppression issues
if ($oldPicture && strpos($oldPicture, 'default.png') === false) {
    // Construct absolute path to old file
    // Assuming oldPicture is stored as relative path like 'assets/uploads/...'
    $oldAbsolutePath = __DIR__ . '/../' . $oldPicture;
    if (file_exists($oldAbsolutePath)) {
        unlink($oldAbsolutePath);
    }
}

// If destination file already exists (same user, same ext), unlink it to be sure
if (file_exists($destination)) {
    unlink($destination);
}

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    sendError("Failed to save uploaded file.");
}

// Relative path for database and frontend
$relativePath = 'assets/uploads/profile_pictures/' . $filename;

// Update database
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("UPDATE users SET PPicture = ? WHERE id = ?");
$stmt->bind_param("si", $relativePath, $userId);

if ($stmt->execute()) {
    // Update session
    $_SESSION['profile_picture'] = $relativePath;
    
    sendSuccess([
        'message' => 'Profile picture updated successfully.'
    ]);
} else {
    // Clean up file if DB update fails
    unlink($destination);
    sendError("Database update failed.");
}

$stmt->close();
$conn->close();
