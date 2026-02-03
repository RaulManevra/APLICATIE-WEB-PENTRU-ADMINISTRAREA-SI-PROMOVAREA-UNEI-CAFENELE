<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../core/output.php';
?>
<div class="login-wrapper">
    <form class="login-box" action="?page=forgot_password_handler" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <input type="hidden" name="action" value="request_reset">
        
        <h3 style="position: relative; z-index: 10; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">Forgot Password?</h3>
        
        <p style="text-align: center; margin-bottom: 30px; font-size: 1rem; color: #fff; position: relative; z-index: 10; text-shadow: 0 1px 2px rgba(0,0,0,0.8);">
            Enter your email address and we'll send you a link to reset your password.
        </p>

        <div class="input-box">
            <input type="email" name="email" required placeholder="Enter your email">
            <i class="fa-solid fa-envelope"></i>
        </div>

        <button type="submit">Send Reset Link</button>

        <p class="bottom-text">
            Remember your password? <a href="?page=login">Login here</a>
        </p>
    </form>
</div>
