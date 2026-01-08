<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/output.php';
require_once __DIR__ . '/../core/SessionManager.php';

class SettingsController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function handleRequest() {
        if (!SessionManager::isLoggedIn()) sendError("Unauthorized");
        // Add admin check if needed strictly
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'get_schedule':
                $this->getSchedule();
                break;
            case 'update_schedule':
                $this->updateSchedule();
                break;
            default:
                sendError("Invalid settings action");
        }
    }

    private function getSchedule() {
        $sql = "SELECT day_of_week, day_name, open_time, close_time, is_closed FROM schedule ORDER BY day_of_week ASC";
        $res = $this->conn->query($sql);
        $schedule = [];
        
        // Ensure 0-6 index
        while($row = $res->fetch_assoc()) {
            $schedule[$row['day_of_week']] = $row;
        }

        // Fill gaps if any (though migration ensures init)
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        for($i=0; $i<=6; $i++) {
            if (!isset($schedule[$i])) {
                $schedule[$i] = [
                    'day_of_week' => $i,
                    'day_name' => $days[$i],
                    'open_time' => '07:00:00',
                    'close_time' => '17:00:00',
                    'is_closed' => ($i === 0) ? 1 : 0
                ];
            }
        }
        
        // Sort by key to be safe
        ksort($schedule);
        
        sendSuccess(['data' => array_values($schedule)]);
    }

    private function updateSchedule() {
        $days = $_POST['schedule'] ?? [];
        if (!is_array($days)) sendError("Invalid data format");

        foreach ($days as $day) {
            $dIndex = intval($day['day_of_week']);
            $open = $day['open_time'];
            $close = $day['close_time'];
            $closed = isset($day['is_closed']) && ($day['is_closed'] == '1' || $day['is_closed'] == 'true') ? 1 : 0;

            // Basic validation
            // 0-6
            if ($dIndex < 0 || $dIndex > 6) continue;

            $stmt = $this->conn->prepare("UPDATE schedule SET open_time = ?, close_time = ?, is_closed = ? WHERE day_of_week = ?");
            $stmt->bind_param("ssii", $open, $close, $closed, $dIndex);
            $stmt->execute();
            $stmt->close();
        }

        sendSuccess(['message' => 'Schedule updated successfully']);
    }
}
