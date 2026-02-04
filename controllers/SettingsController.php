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
            case 'get_general': // New action
                $this->getGeneralSettings();
                break;
            case 'update_general': // New action
                $this->updateGeneralSettings();
                break;
            case 'get_emails':
                $this->getEmailSettings();
                break;
            case 'update_emails':
                $this->updateEmailSettings();
                break;
            case 'get_loyalty':
                $this->getLoyaltySettings();
                break;
            case 'update_loyalty':
                $this->updateLoyaltySettings();
                break;
            default:
                sendError("Invalid settings action");
        }
    }

    private function getGeneralSettings() {
        $sql = "SELECT setting_key, setting_value FROM settings";
        $res = $this->conn->query($sql);
        $settings = [];
        while($row = $res->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        sendSuccess(['data' => $settings]);
    }

    private function updateGeneralSettings() {
        $data = [
            'tva_a' => $_POST['tva_a'] ?? '19',
            'tva_b' => $_POST['tva_b'] ?? '9',
            'tva_c' => $_POST['tva_c'] ?? '5',
            'tva_d' => $_POST['tva_d'] ?? '0'
        ];

        foreach ($data as $key => $val) {
             $this->saveGlobalSetting($key, $val); // Use existing helper? No, that's private in this class for email. Let's make it more generic or duplicate.
             // Actually, Settings usually go to `settings` table (key/value), Global Settings for emails go to `global_settings`...
             // Wait, previous code used `settings` table for TVA in `updateGeneralSettings`.
             // And `email` settings used `global_settings`.
             // `updateGeneralSettings` used `settings` table.
             
            $stmt = $this->conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->bind_param("ss", $key, $val);
            $stmt->execute();
            $stmt->close();
        }
        
        sendSuccess(['message' => 'Settings updated successfully']);
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

    private function getEmailSettings() {
        $settings = ['newsletter_email' => '', 'support_email' => ''];
        $res = $this->conn->query("SELECT key_name, value FROM global_settings WHERE key_name IN ('newsletter_email', 'support_email')");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $settings[$row['key_name']] = $row['value'];
            }
        }
        sendSuccess(['data' => $settings]);
    }

    private function getLoyaltySettings() {
        $defaults = [
            'loyalty_earn_threshold' => '25',
            'loyalty_earn_reward' => '5',
            'loyalty_spend_unit_points' => '10',
            'loyalty_spend_unit_value' => '1',
            'loyalty_max_spend_points' => '100'
        ];
        
        $res = $this->conn->query("SELECT key_name, value FROM global_settings WHERE key_name LIKE 'loyalty_%'");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $defaults[$row['key_name']] = $row['value'];
            }
        }
        sendSuccess(['data' => $defaults]);
    }

    private function updateLoyaltySettings() {
        $keys = [
            'loyalty_earn_threshold', 
            'loyalty_earn_reward', 
            'loyalty_spend_unit_points', 
            'loyalty_spend_unit_value', 
            'loyalty_max_spend_points'
        ];

        foreach ($keys as $key) {
             if (isset($_POST[$key])) {
                 $val = intval($_POST[$key]); // Ensure integer
                 $this->saveGlobalSetting($key, (string)$val);
             }
        }
        sendSuccess(['message' => 'Loyalty settings updated']);
    }

    private function updateEmailSettings() {
        // Auth check happens in handleRequest generally, but can enforce admin here
        $newsletter = $_POST['newsletter_email'] ?? '';
        $support = $_POST['support_email'] ?? '';

        $this->saveGlobalSetting('newsletter_email', $newsletter);
        $this->saveGlobalSetting('support_email', $support);

        sendSuccess(['message' => 'Email settings updated']);
    }

    private function saveGlobalSetting($key, $val) {
        $stmt = $this->conn->prepare("INSERT INTO global_settings (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
        $stmt->bind_param("sss", $key, $val, $val);
        $stmt->execute();
    }
}
