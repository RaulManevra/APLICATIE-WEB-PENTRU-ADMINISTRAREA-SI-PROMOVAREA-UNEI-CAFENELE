<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/output.php';
require_once __DIR__ . '/../core/SessionManager.php';

class ReservationController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function handleRequest() {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'create':
                $this->createReservation();
                break;
            case 'get_all':
                $this->getAllReservations();
                break;
            case 'toggle_blacklist':
                $this->toggleBlacklist(); // Admin only
                break;
            case 'delete':
                $this->deleteReservation(); // Admin Only
                break;
            case 'check_status':
                 // Optional: check status for a specific table/time
                 break;
            default:
                sendError("Invalid action.");
        }
    }

    private function createReservation() {
        if (!SessionManager::isLoggedIn()) {
            sendError("You must be logged in to make a reservation.");
        }

        $user = SessionManager::getCurrentUserData();
        $userId = $user['id'];

        // Check if Blacklisted
        $stmt = $this->conn->prepare("SELECT is_blacklisted, blacklist_reason FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if ($row['is_blacklisted']) {
                sendError("You are restricted from making reservations. Please talk to an employee for more details.");
            }
        }
        $stmt->close();

        // Validate Inputs
        $tableId = intval($_POST['table_id'] ?? 0);
        $dateStr = $_POST['date'] ?? ''; // YYYY-MM-DD
        $timeStr = $_POST['time'] ?? ''; // HH:MM
        $resName = trim($_POST['name'] ?? '');

        if ($tableId <= 0 || empty($dateStr) || empty($timeStr)) {
            sendError("Invalid table, date, or time.");
        }

        if (empty($resName)) {
            sendError("Please provide a name for the reservation.");
        }
        if (strlen($resName) > 50) {
            sendError("Name is too long (max 50 chars).");
        }
        // Basic sanitization is handled by prepared statement, but let's strip tags to be safe for display
        $resName = htmlspecialchars($resName, ENT_QUOTES, 'UTF-8');

        // Parse Date
        try {
            $fullDateStr = $dateStr . ' ' . $timeStr;
            $date = new DateTime($fullDateStr);
            $now = new DateTime();
            
            if ($date < $now) {
                sendError("Cannot reserve in the past.");
            }
        } catch (Exception $e) {
            sendError("Invalid date/time format.");
        }

        $resTime = $date->format('Y-m-d H:i:s');

        // User Constraint: One reservation per day.
        // Check if user already has a reservation on the requested Date (Y-m-d).
        $targetDate = $date->format('Y-m-d');
        
        $stmt = $this->conn->prepare("SELECT id FROM reservations WHERE user_id = ? AND DATE(reservation_time) = ?");
        $stmt->bind_param("is", $userId, $targetDate);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            sendError("You can only make one reservation per day.");
        }
        $stmt->close();

        //  User Constraint: No pending upcoming reservations.
        // Cannot make a new one if you have one currently active or in the future.
        $oneHourAgo = clone $now;
        $oneHourAgo->modify('-1 hour');
        $checkTimeStr = $oneHourAgo->format('Y-m-d H:i:s');
        
        $stmt = $this->conn->prepare("SELECT id FROM reservations WHERE user_id = ? AND reservation_time > ?");
        $stmt->bind_param("is", $userId, $checkTimeStr);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
             sendError("You already have an upcoming reservation. Please complete it before booking another.");
        }
        $stmt->close();

        // Overlap Check: Minimum 20 mins between reservations for same table
        // Existing R: [Time, Time + ~1h (implied duration?)]
        // User said: "turn back green one hour after". So reservation is effectively 1 hour?
        // "Reservations should have a minimum 20 minutes between them".
        // This implies if Res A is 14:00, Res B cannot be 14:10.
        // If Res A is 14:00 (ends 15:00 based on color logic), can Res B be 15:10?
        // Maybe "between them" means buffer?
        // Let's assume standard "slot" is 1 hour? Or indefinite?
        // User instruction: "turn yellow 20 minutes before... turn back green one hour after".
        // This suggests the "event" lasts 1 hour.
        // So we strictly prevent overlap of intervals [Start, Start+1h].
        // AND maybe a 20 min buffer?
        // Let's interpret "minimum 20 minutes between them" as:
        // Gap between End(A) and Start(B) >= 20 mins.
        // If A = 14:00. End(A) = 15:00.
        // Start(B) must be >= 15:20?
        // Or if Start(B) < 14:00? End(B) <= 13:40?
        // Effectively: ABS(TimeA - TimeB) >= (60 + 20) minutes? No, duration is fixed?
        // Let's assume duration is 1 Hour.
        // So we check if `new_start` overlaps with `[existing_start - (1h + 20m), existing_start + (1h + 20m)]`.
        // Wait, simpler:
        // Range A: [StartA, EndA]. Range B: [StartB, EndB].
        // We need Gap >= 20.
        // StartB >= EndA + 20 OR EndB <= StartA - 20.
        // IF duration is 60 mins:
        // StartB >= StartA + 60 + 20 -> StartB >= StartA + 80 mins.
        // StartA >= StartB + 60 + 20 -> StartA >= StartB + 80 mins -> StartB <= StartA - 80 mins.
        // So if ABS(StartA - StartB) < 80 minutes, it is a conflict.

        // Resulting Logic:
        // A conflict occurs if the NEW reservation's "Core" (1h) overlaps with the EXISTING "Total" (1h + 20m grace).
        // Calculation: 
        // Diff = New - Exist.
        // If Diff is in (-80, 60), it is a conflict.
        
        $stmt = $this->conn->prepare("
            SELECT reservation_time FROM reservations 
            WHERE table_id = ? 
            AND TIMESTAMPDIFF(MINUTE, reservation_time, ?) > -80 
            AND TIMESTAMPDIFF(MINUTE, reservation_time, ?) < 60
        ");
        $stmt->bind_param("iss", $tableId, $resTime, $resTime);
        $stmt->execute();
        $conflictRes = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($conflictRes) {
            // Found a conflict.
            $conflictTime = new DateTime($conflictRes['reservation_time']);
            
            // Calculate Earliest Next Slot for THIS table
            // Based on rules, we can start 60 minutes after the existing one starts 
            // (since our grace period can overlap their core).
            $nextSlot = clone $conflictTime;
            $nextSlot->modify('+60 minutes');
            // If the conflict was later than requested (e.g. user asked 14:00, conflict is 14:30),
            // The constraint is simple gap. 
            // Actually, if conflict is 14:30. Start range is [13:10, 15:50].
            // If user asked 14:00.
            // Next slot: 14:30 + 80m = 15:50.
            
            // Find Alternative Tables at REQUESTED time
            // Select tables NOT in (Reservations overlapping ReqTime)
            // And Status = 'Libera' (or 'Inactiva'? No).
            
            $altSql = "
                SELECT t.ID 
                FROM tables t
                WHERE t.Status != 'Inactiva' 
                AND t.ID != ?
                AND t.ID NOT IN (
                    SELECT table_id FROM reservations 
                    WHERE TIMESTAMPDIFF(MINUTE, reservation_time, ?) > -80 
                    AND TIMESTAMPDIFF(MINUTE, reservation_time, ?) < 60
                )
                LIMIT 5
            ";
            $altStmt = $this->conn->prepare($altSql);
            $altStmt->bind_param("iss", $tableId, $resTime, $resTime);
            $altStmt->execute();
            $altRes = $altStmt->get_result();
            $alternatives = [];
            while($row = $altRes->fetch_assoc()) {
                $alternatives[] = $row['ID'];
            }
            $altStmt->close();

            // Structure the error
            sendSuccess([
                'success' => false,
                'conflict' => true,
                'message' => 'Table is reserved at ' . $conflictTime->format('H:i'),
                'conflict_time' => $conflictTime->format('Y-m-d H:i:s'),
                'next_available' => $nextSlot->format('Y-m-d H:i:s'),
                'alternative_tables' => $alternatives
            ]);
        }

        // Insert
        $stmt = $this->conn->prepare("INSERT INTO reservations (user_id, table_id, reservation_time, reservation_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $userId, $tableId, $resTime, $resName);
        
        if ($stmt->execute()) {
            sendSuccess(['message' => 'Reservation confirmed!']);
        } else {
            sendError("Database error.");
        }
    }

    private function getAllReservations() {
        // Admin Only
        $this->requireAdmin();

        // List future and recent past? Or all? Let's limit to recent.
        // And join users.
        $sql = "
            SELECT r.*, u.username, u.email, u.is_blacklisted, u.blacklist_reason 
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            ORDER BY r.reservation_time DESC
            LIMIT 100
        ";
        
        $result = $this->conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        sendSuccess(['data' => $data]);
    }

    private function toggleBlacklist() {
        // Admin Only
        $this->requireAdmin();

        $targetUserId = intval($_POST['user_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($targetUserId <= 0) sendError("Invalid User ID");

        // Get current status
        $stmt = $this->conn->prepare("SELECT is_blacklisted FROM users WHERE id = ?");
        $stmt->bind_param("i", $targetUserId);
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$row = $res->fetch_assoc()) {
            sendError("User not found");
        }
        $currentState = $row['is_blacklisted'];
        $newState = $currentState ? 0 : 1;
        $stmt->close();

        // If Blacklisting, Reason is Required
        if ($newState === 1 && empty($reason)) {
            sendError("A reason is required to blacklist a user.");
        }
        
        // If unblacklisting, maybe clear reason? Or keep history?
        // Let's clear it or keep it? Requirement says "save that reason".
        // If unblocking, maybe nullify? Let's leave it as NULL if unblocking to be clean.
        $newReason = ($newState === 1) ? $reason : null;

        // Update
        $stmt = $this->conn->prepare("UPDATE users SET is_blacklisted = ?, blacklist_reason = ? WHERE id = ?");
        $stmt->bind_param("isi", $newState, $newReason, $targetUserId);
        if ($stmt->execute()) {
            sendSuccess(['message' => 'User blacklist status updated.', 'new_state' => $newState]);
        } else {
            sendError("Update failed.");
        }
    }

    private function deleteReservation() {
        // Admin Only
        $this->requireAdmin();

        $resId = intval($_POST['id'] ?? 0);
        if ($resId <= 0) sendError("Invalid Reservation ID");

        $stmt = $this->conn->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->bind_param("i", $resId);

        if ($stmt->execute()) {
             sendSuccess(['message' => 'Reservation deleted.']);
        } else {
             sendError("Deletion failed.");
        }
    }

    private function requireAdmin() {
        $user = SessionManager::getCurrentUserData();
        if (!$user || !in_array('admin', $user['roles'])) {
            sendError("Unauthorized.");
        }
    }

    /**
     * Helper to get active/upcoming reservation for UI display.
     */
    public static function getUpcomingForUser($conn, $userId) {
        $resCheckSql = "SELECT r.*, t.ID as table_name 
                        FROM reservations r 
                        JOIN tables t ON r.table_id = t.ID
                        WHERE r.user_id = ? 
                        AND r.reservation_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                        ORDER BY r.reservation_time ASC 
                        LIMIT 1";
        $stmt = $conn->prepare($resCheckSql);
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        }
        return null;
    }
}
?>
