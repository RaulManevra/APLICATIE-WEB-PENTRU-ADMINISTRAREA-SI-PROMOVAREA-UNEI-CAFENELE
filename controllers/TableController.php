<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/output.php';

class TableController {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function handleRequest() {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'get_all':
                $this->getAll();
                break;
            case 'update_status':
                $this->updateStatus();
                break;
            case 'update_count':
                $this->updateCount();
                break;
            case 'update_coordinates':
                $this->updateCoordinates();
                break;
            case 'update_details':
                $this->updateDetails();
                break;
            case 'upload_background':
                $this->uploadBackground();
                break;
            default:
                sendError("Invalid action.");
        }
    }

    private function getAll() {
        // 1. Fetch Reservations
        $conn = $this->conn; // shorthand
        // Note: Timezone is already sync'd in db.php
        
        $resSql = "
            SELECT r.table_id, r.reservation_time, u.username, u.email 
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            WHERE r.reservation_time BETWEEN DATE_SUB(NOW(), INTERVAL 1 HOUR) AND DATE_ADD(NOW(), INTERVAL 20 MINUTE)
        ";
        $resResult = $conn->query($resSql);
        $activeRes = [];
        if ($resResult) {
            while($r = $resResult->fetch_assoc()) {
                $activeRes[$r['table_id']] = $r;
            }
        }

        // 2. Fetch Tables
        $sql = "SELECT * FROM tables ORDER BY ID ASC";
        $result = $conn->query($sql);
        $tables = [];
        while ($row = $result->fetch_assoc()) {
            $tid = $row['ID'];
            // Normalize status mainly for correctness
            $row['Status'] = trim($row['Status']);
            
            // Inject Reservation Data
            if (isset($activeRes[$tid])) {
                 $row['active_reservation'] = $activeRes[$tid];
                 // If status allows, we can override or just let frontend decide
                 // Ideally admin sees EVERYTHING.
            }
            $tables[] = $row;
        }

        // Check for background image
        $bgPath = null;
        $dir = __DIR__ . '/../assets/uploads/floor_plan/';
        $extensions = ['png', 'jpg', 'jpeg', 'gif'];
        foreach ($extensions as $ext) {
            if (file_exists($dir . 'layout.' . $ext)) {
                $bgPath = 'assets/uploads/floor_plan/layout.' . $ext . '?t=' . time(); // cache bust
                break;
            }
        }

        sendSuccess(['data' => $tables, 'background' => $bgPath]);
    }

    private function updateCoordinates() {
        $id = intval($_POST['id'] ?? 0);
        $x = floatval($_POST['x'] ?? 0);
        $y = floatval($_POST['y'] ?? 0);
        $w = floatval($_POST['width'] ?? 5);
        $h = floatval($_POST['height'] ?? 5);

        if ($id <= 0) sendError("Invalid ID");
        
        // Clamp values (Percentages)
        $x = max(0, min(100, $x));
        $y = max(0, min(100, $y));
        $w = max(1, min(100, $w)); // Min 1% width
        $h = max(1, min(100, $h)); // Min 1% height

        $stmt = $this->conn->prepare("UPDATE tables SET x_pos=?, y_pos=?, width=?, height=? WHERE ID=?");
        $stmt->bind_param("ddddi", $x, $y, $w, $h, $id);
        
        if($stmt->execute()) {
            sendSuccess(['message' => 'Coordinates and size updated']);
        } else {
            sendError("Failed to update status");
        }
    }

    private function updateDetails() {
         $id = intval($_POST['id'] ?? 0);
         $shape = $_POST['shape'] ?? 'circle';
         $width = floatval($_POST['width'] ?? 5);
         $height = floatval($_POST['height'] ?? 5);

         if ($id <= 0) sendError("Invalid ID");
         
         $allowedShapes = ['circle', 'square', 'rectangle'];
         if (!in_array($shape, $allowedShapes)) sendError("Invalid shape");
         
         // Clamp dimensions (%) 
         $width = max(1, min(100, $width));
         $height = max(1, min(100, $height));

         $stmt = $this->conn->prepare("UPDATE tables SET shape=?, width=?, height=? WHERE ID=?");
         $stmt->bind_param("sddi", $shape, $width, $height, $id);
         
         if($stmt->execute()) {
             sendSuccess(['message' => 'Table details updated']);
         } else {
             sendError("Failed to update details");
         }
    }

    private function uploadBackground() {
        if (!isset($_FILES['image'])) {
            sendError("No file uploaded.");
        }

        $file = $_FILES['image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($ext, $allowed)) {
            sendError("Invalid file type. Only JPG, PNG, GIF allowed.");
        }

        // Target directory
        $targetDir = __DIR__ . '/../assets/uploads/floor_plan/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Fixed name to avoid DB storage requirement
        $targetFile = $targetDir . 'layout.' . $ext;
        
        // Remove existing files with different valid extensions
        foreach($allowed as $e) {
            $f = $targetDir . 'layout.' . $e;
            if (file_exists($f)) unlink($f);
        }

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Return the relative path
            sendSuccess([
                'message' => 'Background uploaded.',
                'path' => 'assets/uploads/floor_plan/layout.' . $ext . '?t=' . time()
            ]);
        } else {
            sendError("Failed to save file.");
        }
    }

    private function updateStatus() {
        $id = intval($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        $allowedStatuses = ['Inactiva', 'Libera', 'Ocupata', 'Rezervata'];

        if ($id <= 0) {
            sendError("Invalid Table ID.");
        }

        if (!in_array($status, $allowedStatuses)) {
            sendError("Invalid Status.");
        }

        $stmt = $this->conn->prepare("UPDATE tables SET Status = ? WHERE ID = ?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            sendSuccess(['message' => 'Table status updated.']);
        } else {
            sendError("Failed to update status.");
        }
    }

    private function updateCount() {
        $targetCount = intval($_POST['count'] ?? 0);

        if ($targetCount < 0) {
            sendError("Count cannot be negative.");
        }

        // Get current count
        $result = $this->conn->query("SELECT MAX(ID) as max_id, COUNT(*) as total FROM tables");
        $row = $result->fetch_assoc();
        $currentMaxId = intval($row['max_id'] ?? 0);
        $currentCount = intval($row['total'] ?? 0); 
        // Note: Using MAX(ID) is safer for deletions usually, but if IDs are not contiguous, 
        // we should just respect the target count relative to existing structure.
        // The requirement says "change the status and the number of tables".
        // Assuming simple increment/decrement from the highest ID.
        
        // Actually, let's just get the count. If target > current, insert N new.
        // If target < current, delete the last N by ID DESC.
        
        // Let's rely on COUNT(*) first to know how many we have.
        
        if ($targetCount === $currentCount) {
             sendSuccess(['message' => 'No change needed.']);
             return;
        }

        if ($targetCount > $currentCount) {
            // Add tables
            $toAdd = $targetCount - $currentCount;
            // We can do a loop or a bulk insert. Loop is safer for auto_increment.
            $stmt = $this->conn->prepare("INSERT INTO tables (Status) VALUES ('Inactiva')");
            for ($i = 0; $i < $toAdd; $i++) {
                $stmt->execute();
            }
            sendSuccess(['message' => "Added $toAdd tables."]);
        } else {
            // Remove tables
            // Removing the ones with highest IDs first
            $toRemove = $currentCount - $targetCount;
            // LIMIT in DELETE is supported in MySQL
            $stmt = $this->conn->prepare("DELETE FROM tables ORDER BY ID DESC LIMIT ?");
            $stmt->bind_param("i", $toRemove);
            if ($stmt->execute()) {
                 // Check if we need to reset auto-increment
                 $this->conn->query("ALTER TABLE tables AUTO_INCREMENT = 1");
                 sendSuccess(['message' => "Removed $toRemove tables."]);
            } else {
                 sendError("Failed to remove tables.");
            }
        }
    }
}
