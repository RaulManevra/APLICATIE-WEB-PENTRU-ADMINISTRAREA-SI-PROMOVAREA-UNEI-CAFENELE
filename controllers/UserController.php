<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/output.php';
require_once __DIR__ . '/../core/SessionManager.php';

class UserController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function handleRequest() {
        if (!SessionManager::isLoggedIn()) sendError("Unauthorized");
        // Permissions check should be in admin_handler already but...
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        switch ($action) {
            case 'get_all':
                $this->getAll();
                break;
            case 'get_one':
                $this->getOne();
                break;
            case 'change_role':
                $this->changeRole();
                break;
            default:
                sendError("Invalid user action");
        }
    }

    private function getAll() {
        $search = trim($_POST['search'] ?? $_GET['search'] ?? '');
        
        $sql = "SELECT id, username, email, role, PuncteFidelitate, PPicture, is_blacklisted FROM users";
        
        if (!empty($search)) {
            $sql .= " WHERE username LIKE ? OR email LIKE ?";
        }
        
        $sql .= " ORDER BY id DESC LIMIT 50";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($search)) {
            $searchTerm = "%" . $search . "%";
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
        }
        
        $stmt->execute();
        $res = $stmt->get_result();
        
        $users = [];
        while($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        sendSuccess(['data' => $users]);
    }

    private function getOne() {
        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) sendError("Invalid ID");

        $stmt = $this->conn->prepare("SELECT id, username, email, role, PuncteFidelitate, PPicture, is_blacklisted, blacklist_reason, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($user = $res->fetch_assoc()) {
            // Get stats for user
            // Total Reservations
            // Total Reservations (Active/History)
            $stmt2 = $this->conn->prepare("SELECT COUNT(*) as c FROM reservations WHERE user_id = ? AND status != 'deleted'");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $user['total_reservations'] = $stmt2->get_result()->fetch_assoc()['c'];
            $stmt2->close();
            
            // Deleted Reservations
            $stmtDel = $this->conn->prepare("SELECT COUNT(*) as c FROM reservations WHERE user_id = ? AND status = 'deleted'");
            $stmtDel->bind_param("i", $id);
            $stmtDel->execute();
            $user['deleted_reservations'] = $stmtDel->get_result()->fetch_assoc()['c'];
            $stmtDel->close();

            // Total Orders
            $stmt3 = $this->conn->prepare("SELECT COUNT(*) as c FROM orders WHERE user_id = ?");
            $stmt3->bind_param("i", $id);
            $stmt3->execute();
            $user['total_orders'] = $stmt3->get_result()->fetch_assoc()['c'];
            $stmt3->close();

            sendSuccess(['data' => $user]);
        } else {
            sendError("User not found");
        }
        $stmt->close();
    }
    private function changeRole() {
        // Only ADMIN can change roles
        $currentUser = SessionManager::getCurrentUserData();
        if (!in_array('admin', $currentUser['roles'] ?? [])) {
            sendError("Forbidden. Only Admins can change roles.");
        }

        $userId = intval($_POST['user_id'] ?? 0);
        $newRole = $_POST['new_role'] ?? '';

        if ($userId <= 0) sendError("Invalid User ID");
        
        // Validate Role
        $validRoles = ['user', 'employer', 'admin'];
        if (!in_array($newRole, $validRoles)) {
            sendError("Invalid role selected.");
        }

        // Prevent self-demotion (optional, but good practice to warn or allow)
        if ($userId === $currentUser['id'] && $newRole !== 'admin') {
             // If admin demotes themselves, they might lose access.
             // Allow it, but maybe client side warns? 
             // We'll allow it.
        }

        $stmt = $this->conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $newRole, $userId);
        
        if ($stmt->execute()) {
            sendSuccess(['message' => "User role updated to '$newRole'."]);
        } else {
            sendError("Failed to update role: " . $stmt->error);
        }
    }
}
