<?php
require_once __DIR__ . '/config/db.php';

echo "Current Server Time: " . date('Y-m-d H:i:s') . "\n";
echo "Fetching Reservations...\n";

$res = $conn->query("SELECT id, reservation_time, reservation_time >= NOW() as is_upcoming FROM reservations");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | Time: " . $row['reservation_time'] . " | Upcoming: " . ($row['is_upcoming'] ? 'YES' : 'NO') . "\n";
}
