<?php
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../core/output.php';
?>
<link rel="stylesheet" href="assets/css/register.css">
<div class="register-wrapper">
<form class="register-box" action="controllers/register_handler.php" method="post" autocomplete="on">
    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

    <h3>Register</h3>

    <div class="input-box">
        <input id="username" type="text" name="username" autocomplete="username" pattern="[a-z_.]{3,15}" title="Username must be 3-15 characters long and contain only lowercase letters, underscores, and dots." required placeholder="Username">
        <i class="fa-solid fa-user" aria-hidden="true"></i>
    </div>

    <div class="input-box">
        <input id="email" type="email" name="email" autocomplete="email" required placeholder="Email">
        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
    </div>

    <div class="input-box">
        <input id="psw" type="password" name="psw" autocomplete="new-password" required placeholder="Password">
        <i class="fa-solid fa-lock" aria-hidden="true"></i>
    </div>

    <div class="input-box">
        <input id="psw-repeat" type="password" name="psw-repeat" autocomplete="new-password" required placeholder="Repeat password">
        <i class="fa-solid fa-lock" aria-hidden="true"></i>
    </div>

    <button type="submit">Register</button>

    <div class="oauth-section">
        <p>OR</p>
        <a href="controllers/google_auth.php?action=login" class="btn-google">
            <i class="fa-brands fa-google"></i> Sign up with Google
        </a>
    </div>

    <p class="bottom-text">
    Already have an account? <a href="?page=login">Login here</a>
</p>
</form>
</div>
