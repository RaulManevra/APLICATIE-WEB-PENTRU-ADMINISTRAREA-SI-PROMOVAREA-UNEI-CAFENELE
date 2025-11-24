<?php
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/output.php';
?>
<form action="include/login_handler.php" method="post" autocomplete="on">
    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

    <label for="uname"><b>Username or Email</b></label>
    <input id="uname" type="text" name="uname" autocomplete="username" required>

    <label for="psw"><b>Password</b></label>
    <input id="psw" type="password" name="psw" autocomplete="current-password" required>

    <button type="submit">Login</button>
</form>
