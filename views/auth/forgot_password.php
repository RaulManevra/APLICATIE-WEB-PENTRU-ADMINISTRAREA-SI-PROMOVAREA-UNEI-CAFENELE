<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
require_once __DIR__ . '/../../core/csrf.php';
?>
<div class="login-wrapper">
    <form class="login-box" action="?page=forgot_password_handler" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <input type="hidden" name="action" value="request_reset">
        
        <h3>Reset Password</h3>
        <p style="color: #fff; margin-bottom: 20px; text-align: center;">Enter your email to receive a reset link.</p>

        <div class="input-box">
            <input type="email" name="email" required placeholder="Enter Email">
            <i class="fa-solid fa-envelope"></i>
        </div>

        <button type="submit">Send Reset Link</button>

        <p class="bottom-text">
            Remembered? <a href="?page=login">Login here</a>
        </p>
    </form>
</div>
