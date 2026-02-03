<?php
require_once __DIR__ . '/../config/db.php';

try {
    // 1. Add checked_in column
    $checkCol = "SHOW COLUMNS FROM reservations LIKE 'checked_in'";
    $res = $conn->query($checkCol);
    if ($res->num_rows == 0) {
        $sql = "ALTER TABLE reservations ADD COLUMN checked_in TINYINT(1) DEFAULT 0";
        if ($conn->query($sql)) {
            echo "Column 'checked_in' added successfully.\n";
        } else {
            echo "Error adding column: " . $conn->error . "\n";
        }
    } else {
        echo "Column 'checked_in' already exists.\n";
    }

    // 2. Update status ENUM
    // Note: This operation might be slow on large tables or lock it, but for development it's fine.
    // Existing: enum('active','deleted')
    // Target: enum('active','deleted','closed')
    $sqlEnum = "ALTER TABLE reservations MODIFY COLUMN status ENUM('active','deleted','closed') DEFAULT 'active'";
    if ($conn->query($sqlEnum)) {
        echo "Status ENUM updated successfully.\n";
    } else {
        echo "Error updating ENUM: " . $conn->error . "\n";
    }

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
