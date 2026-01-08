<?php
require_once __DIR__ . '/config/db.php';

// 1. Admin Notes Table
$sql1 = "CREATE TABLE IF NOT EXISTS admin_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if ($conn->query($sql1)) {
    echo "admin_notes table created/checked.\n";
    // Insert default row if empty
    $conn->query("INSERT IGNORE INTO admin_notes (id, content) VALUES (1, '')");
} else {
    echo "Error creating admin_notes: " . $conn->error . "\n";
}

// 2. Global Settings Table
$sql2 = "CREATE TABLE IF NOT EXISTS global_settings (
    key_name VARCHAR(50) PRIMARY KEY,
    value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if ($conn->query($sql2)) {
    echo "global_settings table created/checked.\n";
} else {
    echo "Error creating global_settings: " . $conn->error . "\n";
}

// 3. Schedule Table
$sql3 = "CREATE TABLE IF NOT EXISTS schedule (
    day_of_week INT PRIMARY KEY, -- 0=Sun, 1=Mon, etc.
    day_name VARCHAR(20),
    open_time TIME,
    close_time TIME,
    is_closed TINYINT(1) DEFAULT 0
)";
if ($conn->query($sql3)) {
    echo "schedule table created/checked.\n";
    
    // Seed default schedule if empty
    $res = $conn->query("SELECT COUNT(*) as c FROM schedule");
    $row = $res->fetch_assoc();
    if ($row['c'] == 0) {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $stmt = $conn->prepare("INSERT INTO schedule (day_of_week, day_name, open_time, close_time, is_closed) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($days as $idx => $day) {
            $isClosed = ($idx == 0) ? 1 : 0; // Close on Sunday
            $open = '08:00:00';
            $close = '17:00:00';
            $stmt->bind_param("isssi", $idx, $day, $open, $close, $isClosed);
            $stmt->execute();
        }
        echo "Default schedule seeded.\n";
    }
} else {
    echo "Error creating schedule: " . $conn->error . "\n";
}

echo "Migration Complete.\n";
