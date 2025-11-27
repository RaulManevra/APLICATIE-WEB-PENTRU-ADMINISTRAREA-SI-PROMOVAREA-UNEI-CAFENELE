<?php
declare(strict_types=1);

$host = "localhost";
$user = "root";
$pass = "mysql";
$dbname = "mazi_coffee";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset('utf8mb4'); // important for XSS prevention and proper unicode
