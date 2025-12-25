<?php
declare(strict_types=1);

$host = "127.0.0.1";
$user = "root";
$pass = "mysql";
$dbname = "mazi_coffee";

// WARNING: Hardcoding credentials is not recommended for production.
// Consider using environment variables or a separate config file outside the web root.

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset('utf8mb4'); // important for XSS prevention and proper unicode
