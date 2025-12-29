<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT * FROM slider_images ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $slides = [];
    while ($row = $result->fetch_assoc()) {
        $slides[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $slides]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
