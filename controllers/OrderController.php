<?php
// controllers/OrderController.php
require_once __DIR__ . '/../core/output.php';

class OrderController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function handleRequest() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
             sendError("Unauthorized access.");
        }

        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            case 'get_running':
                $this->getRunningOrders();
                break;
            case 'assign_table':
                $this->assignTable();
                break;
            case 'update_status':
                $this->updateStatus();
                break;
            case 'delete':
                $this->deleteOrder();
                break;
            default:
                sendError("Invalid order action: $action");
        }
    }

    private function getAllOrders() {
        $sql = "SELECT o.id, o.user_id, o.table_id, o.pickup_time, o.status, o.total_price, o.created_at, o.completed_at, u.username, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.pickup_time ASC"; 
        $this->fetchAndSendOrders($sql);
    }

    private function getRunningOrders() {
        $sql = "SELECT o.id, o.user_id, o.table_id, o.pickup_time, o.status, o.total_price, o.created_at, o.completed_at, u.username, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.status NOT IN ('completed', 'cancelled')
                ORDER BY o.pickup_time ASC";
        $this->fetchAndSendOrders($sql);
    }

    private function fetchAndSendOrders($sql) {
        $result = $this->conn->query($sql);
        if (!$result) {
            sendError("DB Error (Orders): " . $this->conn->error);
            return;
        }

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orderId = $row['id'];
            $sqlItems = "SELECT oi.quantity, oi.price_at_time, p.name 
                         FROM order_items oi
                         JOIN products p ON oi.product_id = p.id
                         WHERE oi.order_id = ?";
            $stmtItems = $this->conn->prepare($sqlItems);
            $stmtItems->bind_param("i", $orderId);
            $stmtItems->execute();
            $resItems = $stmtItems->get_result();
            
            $items = [];
            if ($resItems) {
                while ($item = $resItems->fetch_assoc()) {
                    $items[] = $item;
                }
            } else {
                 // Log error or just ignore items?
                 // Let's add an error indicator for debug
                 $items[] = ['name' => 'Error loading items: ' . $this->conn->error, 'quantity' => 0];
            }
            $row['items'] = $items;
            $orders[] = $row;
        }
        sendSuccess(['orders' => $orders]);
    }

    private function updateStatus() {
        $id = $_POST['order_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$id || !$status) {
            sendError("Missing parameters.");
        }

        if (!in_array($status, ['pending', 'preparing', 'ready', 'completed', 'cancelled'])) {
            sendError("Invalid status.");
        }

        // Get current info before update
        $stmtInfo = $this->conn->prepare("SELECT table_id FROM orders WHERE id = ?");
        $stmtInfo->bind_param("i", $id);
        $stmtInfo->execute();
        $resInfo = $stmtInfo->get_result();
        $currentOrder = $resInfo->fetch_assoc();
        $tableId = $currentOrder['table_id'] ?? null;

        $completedAt = null;
        if ($status === 'completed') {
            $completedAt = date('Y-m-d H:i:s');
            // If completed, update both status and completed_at
            $stmt = $this->conn->prepare("UPDATE orders SET status = ?, completed_at = ? WHERE id = ?");
            $stmt->bind_param("ssi", $status, $completedAt, $id);
        } else {
            // Just status
            $stmt = $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
        }

        if ($stmt->execute()) {
             // Logic: If Completed, check if table should be freed
             if ($status === 'completed' && $tableId) {
                 // Check if any other ACTIVE orders exist for this table
                 $chk = $this->conn->prepare("SELECT COUNT(*) as cnt FROM orders WHERE table_id = ? AND status NOT IN ('completed', 'cancelled') AND id != ?");
                 $chk->bind_param("ii", $tableId, $id);
                 $chk->execute();
                 $chkRes = $chk->get_result()->fetch_assoc();
                 
                 if ($chkRes['cnt'] == 0) {
                     // Auto-free table (unless reserved? User said 'set to libera')
                     // Let's check current status to be safe? User rule: "set table as libera exept... another order".
                     // So we just set it to Libera.
                     $updTable = $this->conn->prepare("UPDATE tables SET Status='Libera' WHERE ID=?");
                     $updTable->bind_param("i", $tableId);
                     $updTable->execute();
                 }
             }

             sendSuccess(['message' => 'Order status updated.']);
        } else {
            sendError("Failed to update status.");
        }
    }
    
    private function assignTable() {
        $orderId = $_POST['order_id'] ?? null;
        $tableId = $_POST['table_id'] ?? null; // Can be empty or 'pickup'
        
        if (!$orderId) sendError("Missing Order ID");
        
        if ($tableId === 'pickup' || $tableId === '' || $tableId === 'null') {
            $tableId = null;
        } else {
            $tableId = intval($tableId);
        }

        $stmt = $this->conn->prepare("UPDATE orders SET table_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $tableId, $orderId);
        
        if ($stmt->execute()) {
            // Update Table Status if assigned
            if ($tableId) {
                $updT = $this->conn->prepare("UPDATE tables SET Status='Ocupata' WHERE ID=?");
                $updT->bind_param("i", $tableId);
                $updT->execute();
            }
            sendSuccess(['message' => 'Table assigned']);
        } else {
            sendError("Failed to assign table");
        }
    }

    private function deleteOrder() {
        $id = $_POST['order_id'] ?? null;
        if (!$id) {
            sendError("Missing parameters.");
        }
        
        $stmt = $this->conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
         if ($stmt->execute()) {
             sendSuccess(['message' => 'Order deleted.']);
        } else {
            sendError("Failed to delete order.");
        }
    }
}
