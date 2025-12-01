<?php
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../core/output.php';
?>
<div class="login-wrapper">
<form class="login-box" action="controllers/login_handler.php" method="post" autocomplete="on">
    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

    <label for="uname"><b>Username or Email</b></label>
    <input id="uname" type="text" name="uname" autocomplete="username" required placeholder="Username or Email">

    <label for="psw"><b>Password</b></label>
    <input id="psw" type="password" name="psw" autocomplete="current-password" required placeholder="Passoword">

    <div class="remember">
        <label><input type="checkbox">Remember me</label>
        <a href="#">Forgot password</a>
    </div>

    <button type="submit">Login</button>
    <p style="margin-top:12px; color:#eaeaea;">
        Don't have an account? <a href="?page=register">Register here</a>
    </p>
</form>
</div>
