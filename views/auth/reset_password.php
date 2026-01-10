<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
require_once __DIR__ . '/../../core/csrf.php';
$token = $_GET['token'] ?? '';
?>
<div class="login-wrapper">
    <form class="login-box" action="?page=forgot_password_handler" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
        <input type="hidden" name="action" value="reset_password">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        
        <h3>New Password</h3>

        <div class="input-box">
            <input type="password" name="password" required placeholder="New Password" minlength="6">
            <i class="fa-solid fa-lock"></i>
        </div>

        <button type="submit">Set New Password</button>
    </form>
</div>
