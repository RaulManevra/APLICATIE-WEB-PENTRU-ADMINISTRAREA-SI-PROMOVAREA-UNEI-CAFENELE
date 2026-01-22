<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../core/output.php';
?>
<link rel="stylesheet" href="assets/css/login.css">
<div class="login-wrapper">
<form class="login-box" action="controllers/login_handler.php" method="post" autocomplete="on">
    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
    <h3>Welcome back!</h3>

    <div class="input-box">
        <input id="uname" type="text" name="uname" autocomplete="username" required placeholder="Username or Email">
        <i class="fa-solid fa-user"></i>
    </div>

    <div class="input-box">
        <input id="psw" type="password" name="psw" autocomplete="current-password" required placeholder="Password">
        <i class="fa-solid fa-lock"></i>
    </div>

    <div class="remember">
        <label><input type="checkbox" name="remember_me">Remember me</label>
        <a href="?page=forgot_password">Forgot password</a>
    </div>

    <button type="submit">Login</button>

    <div class="oauth-section">
        <p>OR</p>
        <a href="controllers/google_auth.php?action=login" class="btn-google">
            <i class="fa-brands fa-google"></i> Login with Google
        </a>
    </div>
    <p class="bottom-text">
    Don't have an account? <a href="?page=register">Register here</a>
</p>
</form>
</div>
