<?php
$host = "localhost";
$user = "root";       
$pass = "mysql";           
$dbname = "mazi_coffee";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Conexiune esuata: " . $conn->connect_error);
}
?>