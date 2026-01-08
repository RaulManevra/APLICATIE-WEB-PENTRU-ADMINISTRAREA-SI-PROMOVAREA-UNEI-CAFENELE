<?php
require_once __DIR__ . '/config/db.php';

echo "Checking Orders Table...\n";

// 1. Check Schema (specifically for created_at or just pickup_time)
$res = $conn->query("DESCRIBE orders");
$hasCreatedAt = false;
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo " - " . $row['Field'] . " (" . $row['Type'] . ")\n";
        if ($row['Field'] === 'created_at') $hasCreatedAt = true;
    }
} else {
    echo "Orders table missing. Creating...\n";
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        pickup_time DATETIME,
        total_price DECIMAL(10,2),
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql)) {
        echo "Created orders table.\n";
        $hasCreatedAt = true;
    } else {
        echo "Error creating orders: " . $conn->error . "\n";
    }
}

// 2. Mock Data
$res = $conn->query("SELECT COUNT(*) as c FROM orders");
$count = $res->fetch_assoc()['c'];
if ($count == 0) {
    echo "Seeding/Mocking orders for chart...\n";
    // Seed last 7 days distribution
    for ($i = 0; $i < 7; $i++) {
        // Random number of orders per day (0-5)
        $num = rand(0, 5);
        for ($j = 0; $j < $num; $j++) {
             // Mock data
             $date = date('Y-m-d H:i:s', strtotime("-$i days"));
             $conn->query("INSERT INTO orders (user_id, pickup_time, total_price, status, created_at) 
                           VALUES (1, '$date', " . rand(20, 100) . ", 'completed', '$date')");
        }
    }
    echo "Seeded mock orders.\n";
} else {
    echo "Orders exist ($count).\n";
}
