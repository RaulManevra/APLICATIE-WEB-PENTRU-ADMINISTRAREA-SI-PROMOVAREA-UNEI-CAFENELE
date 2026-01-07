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
        if (!isset($_SESSION['user_id'])) {
             // For now require login.
             // If handling guest checkout, we'd need email/name in post.
             sendError("You must be logged in to checkout.");
        }

        $userId = $_SESSION['user_id'];
        $pickupTimeStr = $_POST['pickup_time'] ?? '';

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
            
            // Optional: Check if store is open (hardcoded 8-22 for example?)
            // For now, let's keep it simple.
            
        } catch (Exception $e) {
            sendError("Invalid date format.");
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
            $stmt = $this->conn->prepare("INSERT INTO orders (user_id, pickup_time, total_price, status) VALUES (?, ?, ?, 'pending')");
            $formattedTime = $pickupTime->format('Y-m-d H:i:s');
            $stmt->bind_param("isd", $userId, $formattedTime, $totalPrice);
            
            if (!$stmt->execute()) {
                throw new Exception("Order creation failed: " . $stmt->error);
            }
            $orderId = $stmt->insert_id;
            $stmt->close();

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
            
            sendSuccess(['message' => 'Order placed successfully!', 'order_id' => $orderId]);

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
}
