<form action="include/login_handler.php" method="post">
    <input type="hidden" name="type" value="login">

    <label>Username or Email</label>
    <input type="text" name="uname" required>

    <label>Password</label>
    <input type="password" name="psw" required>

    <button type="submit">Login</button>
</form>
