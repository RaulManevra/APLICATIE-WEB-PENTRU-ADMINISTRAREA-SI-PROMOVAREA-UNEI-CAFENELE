<form action="include/register_handler.php" method="post">
    <input type="hidden" name="type" value="register">

    <label>Email</label>
    <input type="text" name="email" required>

    <label>Password</label>
    <input type="password" name="psw" required>

    <label>Repeat Password</label>
    <input type="password" name="psw-repeat" required>

    <button type="submit">Register</button>
</form>