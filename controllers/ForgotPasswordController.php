<?php
require_once __DIR__ . '/../core/output.php';

class ForgotPasswordController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function handleRequest() {
        $action = $_POST['action'] ?? '';

        if ($action === 'request_reset') {
            $this->requestReset();
        } elseif ($action === 'reset_password') {
            $this->resetPassword();
        } else {
            sendError("Invalid action.");
        }
    }

    private function requestReset() {
        $email = $_POST['email'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendError("Invalid email address.");
        }

        // Check if user exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            // Security: Don't reveal if user exists.
            sendSuccess(['message' => 'If this email exists, a reset link has been sent.']);
        }

        // Generate Token
        $token = bin2hex(random_bytes(32));
        
        // Store Token
        $stmt = $this->conn->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $token);
        if (!$stmt->execute()) {
            sendError("System error. Try again later.");
        }

        // Get Support Email
        $sender = 'support@mazicoffee.com';
        $res = $this->conn->query("SELECT value FROM global_settings WHERE key_name = 'support_email'");
        if ($row = $res->fetch_assoc()) {
            if (!empty($row['value'])) $sender = $row['value'];
        }

        // Send Email
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER["REQUEST_URI"], '?') . "?page=reset_password&token=" . $token;
        $subject = "Password Reset Request - Mazi Coffee";
        $message = "Click the link below to reset your password:<br><a href='$resetLink'>$resetLink</a><br><br>If you did not request this, ignore this email.";
        $headers = "From: " . $sender . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (@mail($email, $subject, $message, $headers)) {
            sendSuccess(['message' => 'If this email exists, a reset link has been sent.']);
        } else {
            // Strict error for SMTP
             sendError("Failed to send email. SMTP server might not be configured.");
        }
    }

    private function resetPassword() {
        $token = $_POST['token'] ?? '';
        $pass = $_POST['password'] ?? '';
        
        if (empty($token) || empty($pass)) sendError("Missing token or password.");
        if (strlen($pass) < 6) sendError("Password must be at least 6 characters.");

        // Validate Token
        $stmt = $this->conn->prepare("SELECT email FROM password_resets WHERE token = ? AND created_at > (NOW() - INTERVAL 1 HOUR)");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $email = $row['email'];
            
            // Update Password
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $update = $this->conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hash, $email);
            
            if ($update->execute()) {
                // Delete used token
                $this->conn->query("DELETE FROM password_resets WHERE email = '$email'");
                sendSuccess(['message' => 'Password reset successfully. You can now login.', 'redirect' => '?page=login']);
            } else {
                sendError("Failed to update password.");
            }
        } else {
            sendError("Invalid or expired token.");
        }
    }
}
?>
