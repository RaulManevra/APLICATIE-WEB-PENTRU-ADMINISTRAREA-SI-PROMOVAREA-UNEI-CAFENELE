<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Adjust path to db config if needed
require_once __DIR__ . '/config/db.php';

echo "Connected to DB: " . (isset($conn) ? "Yes" : "No") . "\n";

$tables = ['reservations', 'products', 'tables', 'users', 'admin_notes', 'global_settings', 'schedule'];

foreach ($tables as $t) {
    $res = $conn->query("SELECT * FROM $t LIMIT 1");
    if ($res) {
        echo "Table '$t': OK\n";
    } else {
        echo "Table '$t': ERROR - " . $conn->error . "\n";
    }
}
