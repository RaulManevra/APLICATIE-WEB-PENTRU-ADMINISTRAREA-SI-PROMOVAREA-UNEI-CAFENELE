<?php
// controllers/OrderController.php
require_once __DIR__ . '/../core/output.php';

class OrderController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        // Public or User actions
        if ($action === 'get_my_orders') {
            $this->checkUserAuth();
            $this->getUserOrders();
            return;
        }

        // Admin only actions
        $this->checkAdminAuth();

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

    private function checkUserAuth() {
        if (!isset($_SESSION['user_id'])) {
            sendError("Unauthorized access.");
        }
    }

    private function checkAdminAuth() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
             sendError("Unauthorized access.");
        }
    }

    private function getUserOrders() {
        $userId = $_SESSION['user_id'];
        $sql = "SELECT o.id, o.pickup_time, o.status, o.total_price, o.payment_method, o.created_at, o.completed_at, o.points_spent, o.points_earned 
                FROM orders o 
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC, o.id DESC"; 
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            sendError("DB Error (Orders): " . $this->conn->error);
            return;
        }

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orderId = $row['id'];
            // Fetch items for each order
            $sqlItems = "SELECT oi.quantity, oi.price_at_time, p.name, p.image_path
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
            }
            $row['items'] = $items;
            $orders[] = $row;
        }
        sendSuccess(['orders' => $orders]);
    }

    private function getAllOrders() {
        $sql = "SELECT o.id, o.user_id, o.table_id, o.pickup_time, o.status, o.total_price, o.payment_method, o.created_at, o.completed_at, o.points_spent, o.points_earned, u.username, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.pickup_time ASC"; 
        $this->fetchAndSendOrders($sql);
    }

    private function getRunningOrders() {
        $sql = "SELECT o.id, o.user_id, o.table_id, o.pickup_time, o.status, o.total_price, o.payment_method, o.created_at, o.completed_at, o.points_spent, o.points_earned, u.username, u.email 
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
            
            // --- LOYALTY POINTS LOGIC ---
            // Only award if not already completed (simple check: completed_at was null)
            // But here we are updating it. Let's rely on previous state check if possible, or just proceed.
            // Better: Check if points_earned is 0 to avoid double counting? 
            // Or check $currentOrder status? We didn't fetch status before, only table_id.
            
            // Start Transaction for Points
            // $this->conn->begin_transaction(); // Optional but safer
            
            // Get Order Total and User ID
            $stmtOrd = $this->conn->prepare("SELECT user_id, total_price, points_earned FROM orders WHERE id = ?");
            $stmtOrd->bind_param("i", $id);
            $stmtOrd->execute();
            $ordData = $stmtOrd->get_result()->fetch_assoc();
            
            if ($ordData && $ordData['user_id'] && $ordData['points_earned'] == 0) {
                 $uId = $ordData['user_id'];
                 $total = floatval($ordData['total_price']);
                 
                 // Fetch Settings
                 $settings = [];
                 $resSet = $this->conn->query("SELECT key_name, value FROM global_settings WHERE key_name LIKE 'loyalty_%'");
                 while ($row = $resSet->fetch_assoc()) $settings[$row['key_name']] = $row['value'];
                 
                 $threshold = intval($settings['loyalty_earn_threshold'] ?? 25);
                 $reward = intval($settings['loyalty_earn_reward'] ?? 5);
                 
                 if ($threshold > 0 && $total >= $threshold) {
                     $points = floor($total / $threshold) * $reward;
                     
                     if ($points > 0) {
                         // Award Points to User
                         $updU = $this->conn->prepare("UPDATE users SET PuncteFidelitate = COALESCE(PuncteFidelitate, 0) + ? WHERE id = ?");
                         $updU->bind_param("ii", $points, $uId);
                         $updU->execute();
                         
                         // Record in Order (so we don't award again)
                         // We do this in the main UPDATE or separate? Separate is fine for now but main UPDATE is already prepared above.
                         // Actually, I can just execute the main update first, then this.
                         
                         $updOrd = $this->conn->prepare("UPDATE orders SET points_earned = ? WHERE id = ?");
                         $updOrd->bind_param("ii", $points, $id);
                         $updOrd->execute();
                     }
                 }
            }
            // $this->conn->commit();
        } else {
            // Just status
            $stmt = $this->conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
        }

        if ($stmt->execute()) {
             // Logic: If Completed, check if table should be freed
             if ($status === 'completed') {
                 // 1. FREE TABLE LOGIC
                 if ($tableId) {
                     $chk = $this->conn->prepare("SELECT COUNT(*) as cnt FROM orders WHERE table_id = ? AND status NOT IN ('completed', 'cancelled') AND id != ?");
                     $chk->bind_param("ii", $tableId, $id);
                     $chk->execute();
                     $chkRes = $chk->get_result()->fetch_assoc();
                     
                     if ($chkRes['cnt'] == 0) {
                         $updTable = $this->conn->prepare("UPDATE tables SET Status='Libera' WHERE ID=?");
                         $updTable->bind_param("i", $tableId);
                         $updTable->execute();
                     }
                 }

                 // 2. STOCK DECREMENT LOGIC
                 try {
                     $itemsQ = $this->conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                     $itemsQ->bind_param("i", $id);
                     $itemsQ->execute();
                     $itemsRes = $itemsQ->get_result();
                     
                     if ($itemsRes) {
                         // Prepare update statement for ingredients
                         // Since we normalized to base units in ProductController, we can subtract directly.
                         $updStock = $this->conn->prepare("UPDATE ingredients SET stock_amount = stock_amount - ? WHERE id = ?");
                         
                         // Prepare link fetcher
                         $linkQ = $this->conn->prepare("SELECT ingredient_id, quantity FROM product_ingredients WHERE product_id = ?");
                         
                         while ($item = $itemsRes->fetch_assoc()) {
                             $pid = $item['product_id'];
                             $orderQty = floatval($item['quantity']);
                             
                             $linkQ->bind_param("i", $pid);
                             $linkQ->execute();
                             $links = $linkQ->get_result();
                             
                             while ($link = $links->fetch_assoc()) {
                                 $ingId = $link['ingredient_id'];
                                 $requiredPerUnit = floatval($link['quantity']);
                                 $totalDeduct = $orderQty * $requiredPerUnit;
                                 
                                 $updStock->bind_param("di", $totalDeduct, $ingId);
                                 $updStock->execute();
                             }
                         }
                     }
                 } catch (Exception $e) {
                     // Log error but don't fail the request?
                     error_log("Stock Update Error: " . $e->getMessage());
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
