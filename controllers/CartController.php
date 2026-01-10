<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/output.php';

class CartController {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function handleRequest() {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'add':
                $this->addToCart();
                break;
            case 'remove':
                $this->removeFromCart();
                break;
            case 'update_quantity':
                $this->updateQuantity();
                break;
            case 'get_cart':
                $this->getCart();
                break;
            case 'clear':
                $this->clearCart();
                break;
            case 'checkout':
                $this->checkout();
                break;
            default:
                sendError("Invalid cart action.");
        }
    }

    private function addToCart() {
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);

        if ($productId <= 0) {
            sendError("Invalid product ID.");
        }
        if ($quantity <= 0) {
            sendError("Invalid quantity.");
        }

        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }

        sendSuccess([
            'message' => 'Product added to cart.',
            'total_items' => array_sum($_SESSION['cart'])
        ]);
    }

    private function removeFromCart() {
        $productId = intval($_POST['product_id'] ?? 0);
        if ($productId <= 0) sendError("Invalid product ID.");

        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }

        sendSuccess([
            'message' => 'Item removed from cart.',
            'total_items' => array_sum($_SESSION['cart'])
        ]);
    }

    private function updateQuantity() {
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);

        if ($productId <= 0) sendError("Invalid product ID.");

        if ($quantity <= 0) {
            // If quantity is 0 or less, remove item
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }

        sendSuccess([
            'message' => 'Cart updated.',
            'total_items' => array_sum($_SESSION['cart'])
        ]);
    }

    private function clearCart() {
        $_SESSION['cart'] = [];
        sendSuccess(['message' => 'Cart cleared.']);
    }

    private function checkout() {
        // Fix for "Network Error": Suppress HTML output errors that break JSON
        ini_set('display_errors', '0'); 
        error_reporting(E_ALL);

        $pickupTimeStr = $_POST['pickup_time'] ?? '';
        $token = $_POST['token'] ?? '';
        
        $tableId = null;
        $formattedTime = null;
        $userId = $_SESSION['user_id'] ?? null;

        // --- TOKEN HANDLING (Moved Up) ---
        $isTableOrder = false;

        if (!empty($token)) {
            $decoded = base64_decode($token);
            // Expected format: "Table {id}" or "Website"
            
            if (strpos($decoded, 'Table ') === 0) {
                // It is a Table Order
                $isTableOrder = true;
                $parts = explode(' ', $decoded);
                if (isset($parts[1])) {
                    $tableId = intval($parts[1]);
                }
                
                // For Table orders, Pickup Time is NOW (Immediate)
                $now = new DateTime();
                $formattedTime = $now->format('Y-m-d H:i:s');
            }
        }

        // --- AUTH CHECK ---
        if (!$userId) {
            if ($isTableOrder) {
                // Allow Guest Checkout for Table Orders
                $userId = $this->getOrCreateGuestUser();
            } else {
                sendError("You must be logged in to checkout.");
            }
        }

        // Standard Pickup Flow (Website or No Token)
        if (!$isTableOrder) {
            if (empty($pickupTimeStr)) {
                sendError("Pickup time is required.");
            }
    
            // Validate Pickup Time
            try {
                $pickupTime = new DateTime($pickupTimeStr);
                $now = new DateTime();
                
                // Allow only future times + buffer (e.g. 15 mins)
                $buffer = clone $now;
                $buffer->modify('+15 minutes');
                
                if ($pickupTime < $buffer) {
                     sendError("Pickup time must be at least 15 minutes from now.");
                }
                
                // --- WORKING HOURS CHECK ---
                $dayOfWeek = (int)$pickupTime->format('w');
                
                $stmtS = $this->conn->prepare("SELECT open_time, close_time, is_closed FROM schedule WHERE day_of_week = ?");
                $stmtS->bind_param("i", $dayOfWeek);
                $stmtS->execute();
                $schedule = $stmtS->get_result()->fetch_assoc();
                $stmtS->close();
        
                if (!$schedule) {
                    if ($dayOfWeek === 0) $schedule = ['is_closed' => 1];
                    elseif ($dayOfWeek === 6) $schedule = ['is_closed' => 0, 'open_time' => '08:00:00', 'close_time' => '17:00:00'];
                    else $schedule = ['is_closed' => 0, 'open_time' => '07:00:00', 'close_time' => '17:00:00'];
                }
        
                if ($schedule['is_closed']) {
                    sendError("We are closed on " . $pickupTime->format('l') . "s.");
                }
        
                $openTime = new DateTime($pickupTime->format('Y-m-d') . ' ' . $schedule['open_time']);
                $closeTime = new DateTime($pickupTime->format('Y-m-d') . ' ' . $schedule['close_time']);
                
                $lastPickup = clone $closeTime;
                $lastPickup->modify('-15 minutes');
        
                if ($pickupTime < $openTime || $pickupTime > $lastPickup) {
                    sendError("Pickup available between " . $openTime->format('H:i') . " and " . $lastPickup->format('H:i') . ".");
                }
                
                $formattedTime = $pickupTime->format('Y-m-d H:i:s');
                
            } catch (Exception $e) {
                sendError("Invalid date format or schedule check failed.");
            }
        }

        if (empty($_SESSION['cart'])) {
            sendError("Cart is empty.");
        }

        // Calculate total and prepare items
        $ids = array_keys($_SESSION['cart']);
        $idsString = implode(',', array_map('intval', $ids));
        $sql = "SELECT id, price FROM products WHERE id IN ($idsString)";
        $result = $this->conn->query($sql);

        $orderItems = [];
        $totalPrice = 0;

        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $qty = $_SESSION['cart'][$id];
            $price = floatval($row['price']);
            $subtotal = $price * $qty;
            
            $totalPrice += $subtotal;
            $orderItems[] = [
                'product_id' => $id,
                'quantity' => $qty,
                'price' => $price
            ];
        }

        // Transaction
        $this->conn->begin_transaction();

        try {
            // Updated INSERT to include table_id
            $stmt = $this->conn->prepare("INSERT INTO orders (user_id, pickup_time, total_price, status, table_id) VALUES (?, ?, ?, 'pending', ?)");
            $stmt->bind_param("isdi", $userId, $formattedTime, $totalPrice, $tableId);
            
            if (!$stmt->execute()) {
                throw new Exception("Order creation failed: " . $stmt->error);
            }
            $orderId = $stmt->insert_id;
            $stmt->close();
            
            // If Table Order, update table status to 'Ocupata'? 
            // The prompt says: "will auto-select that table for the order". 
            // In Admin "Running Orders" we implemented logic to set 'Ocupata' when assigning.
            // Should we do it here? Yes, consistent.
            if ($tableId) {
                $updT = $this->conn->prepare("UPDATE tables SET Status='Ocupata' WHERE ID=?");
                $updT->bind_param("i", $tableId);
                $updT->execute();
            }

            // Insert Items
            $stmtItems = $this->conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
            foreach ($orderItems as $item) {
                $stmtItems->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
                if (!$stmtItems->execute()) {
                    throw new Exception("Order item creation failed.");
                }
            }
            $stmtItems->close();

            $this->conn->commit();
            
            // Clear Cart
            $_SESSION['cart'] = [];
            
            // Custom Message
            $msg = $isTableOrder ? "Order sent to kitchen (Table $tableId)." : "Order placed successfully!";
            
            sendSuccess(['message' => $msg, 'order_id' => $orderId]);

        } catch (Exception $e) {
            $this->conn->rollback();
            sendError($e->getMessage());
        }
    }

    private function getCart() {
        if (empty($_SESSION['cart'])) {
            sendSuccess(['items' => [], 'total' => 0]);
        }

        $ids = array_keys($_SESSION['cart']);
        if (empty($ids)) {
             sendSuccess(['items' => [], 'total' => 0]);
        }

        $idsString = implode(',', array_map('intval', $ids));
        
        $sql = "SELECT id, name, price, image_path FROM products WHERE id IN ($idsString)";
        $result = $this->conn->query($sql);

        $items = [];
        $grandTotal = 0;

        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $qty = $_SESSION['cart'][$id];
            $subtotal = $row['price'] * $qty;
            
            $row['quantity'] = $qty;
            $row['subtotal'] = $subtotal;
            
            $items[] = $row;
            $grandTotal += $subtotal;
        }

        sendSuccess([
            'items' => $items,
            'total' => $grandTotal,
            'count' => array_sum($_SESSION['cart'])
        ]);
    }


    private function getOrCreateGuestUser() {
        // defined guest email
        $email = 'guest@mazicoffee.com';
        
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = 'Guest'");
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            return $row['id'];
        }
        
        // Create Guest
        $pass = password_hash('guest123', PASSWORD_DEFAULT);
        $stmt2 = $this->conn->prepare("INSERT INTO users (username, email, password, role) VALUES ('Guest', ?, ?, 'user')");
        $stmt2->bind_param("ss", $email, $pass);
        if ($stmt2->execute()) {
             return $stmt2->insert_id;
        }
        
        throw new Exception("Failed to provision Guest account.");
    }
}
