<?php
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/output.php';
?>
<form action="include/register_handler.php" method="post" autocomplete="on">
    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

    <label for="email"><b>Email</b></label>
    <input id="email" type="email" name="email" autocomplete="email" required>

    <label for="psw"><b>Password</b></label>
    <input id="psw" type="password" name="psw" autocomplete="new-password" required>

    <label for="psw-repeat"><b>Repeat Password</b></label>
    <input id="psw-repeat" type="password" name="psw-repeat" autocomplete="new-password" required>

    <button type="submit">Register</button>
</form>
