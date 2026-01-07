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
            case 'get_all':
                $this->getAllOrders();
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
        $sql = "SELECT o.id, o.user_id, o.pickup_time, o.status, o.total_price, o.created_at, u.username, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.pickup_time ASC"; // Show earliest pickup first
        $result = $this->conn->query($sql);

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            // Fetch items for each order
            $orderId = $row['id'];
            $sqlItems = "SELECT oi.quantity, oi.price_at_time, p.name 
                         FROM order_items oi
                         JOIN products p ON oi.product_id = p.id
                         WHERE oi.order_id = $orderId";
            $resItems = $this->conn->query($sqlItems);
            $items = [];
            while ($item = $resItems->fetch_assoc()) {
                $items[] = $item;
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

        if (!in_array($status, ['pending', 'completed', 'cancelled'])) {
            sendError("Invalid status.");
        }

        $stmt = $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
             sendSuccess(['message' => 'Order status updated.']);
        } else {
            sendError("Failed to update status.");
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
