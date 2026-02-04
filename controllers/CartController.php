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
            case 'get_public_key':
                $this->getPublicKey();
                break;
            case 'checkout':
                $this->checkoutEncrypted();
                break;
            case 'validate_checkout':
                $this->validate_checkout();
                break;
            case 'apply_points': // NEW
                $this->applyPoints();
                break;
            default:
                sendError("Invalid cart action.");
        }
    }

    // ... existing getPublicKey ...

    private function applyPoints() {
        $points = intval($_POST['points'] ?? 0);
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) sendError("Please login to use points.");
        
        $config = $this->getLoyaltyConfig();
        $userPoints = $this->getUserPoints($userId);
        
        if ($points < 0) $points = 0;
        
        // Max limit check
        $maxSpend = intval($config['loyalty_max_spend_points'] ?? 100);
        if ($points > $maxSpend) {
            sendError("Maximum points per order is $maxSpend.");
        }
        
        // Balance check
        if ($points > $userPoints) {
            sendError("Insufficient points balance.");
        }
        
        // Unit check (must be multiple of unit, e.g. 10)
        $unit = intval($config['loyalty_spend_unit_points'] ?? 10);
        if ($points % $unit !== 0) {
            sendError("Points must be used in blocks of $unit.");
        }
        
        $_SESSION['cart_points'] = $points;
        sendSuccess(['message' => 'Points applied.', 'points' => $points]);
    }

    private function getLoyaltyConfig() {
        $defaults = [
            'loyalty_spend_unit_points' => 10,
            'loyalty_spend_unit_value' => 1,
            'loyalty_max_spend_points' => 100
        ];
        $res = $this->conn->query("SELECT key_name, value FROM global_settings WHERE key_name LIKE 'loyalty_%'");
        while ($row = $res->fetch_assoc()) {
            $defaults[$row['key_name']] = intval($row['value']); // Cast directly to int for calc
        }
        return $defaults;
    }

    private function getUserPoints($uid) {
        $stmt = $this->conn->prepare("SELECT PuncteFidelitate FROM users WHERE id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return intval($res['PuncteFidelitate'] ?? 0);
    }

    private function getPublicKey() {
        if (!isset($_SESSION['rsa_private_key'])) {
            $config = array(
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            );
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $config["config"] = "d:\\Apps\\Ampps\\apache\\conf\\openssl.cnf";
            }
            $res = openssl_pkey_new($config);
            if (!$res) sendError("Encryption setup failed: " . openssl_error_string());
            openssl_pkey_export($res, $privateKey, null, $config);
            $_SESSION['rsa_private_key'] = $privateKey;
            $keyDetails = openssl_pkey_get_details($res);
            $_SESSION['rsa_public_key'] = $keyDetails['key'];
        }
        sendSuccess(['publicKey' => $_SESSION['rsa_public_key']]);
    }

    private function checkoutEncrypted() {
        ini_set('display_errors', '0'); error_reporting(E_ALL);
        $encryptedKeyB64 = $_POST['encrypted_key'] ?? '';
        $encryptedDataB64 = $_POST['encrypted_data'] ?? '';
        $ivB64 = $_POST['iv'] ?? '';

        if (empty($encryptedKeyB64) || empty($encryptedDataB64) || empty($ivB64)) {
            if (isset($_POST['payment_method']) && !isset($_POST['encrypted_key'])) {
                $this->checkoutLegacy(); return;
            }
            sendError("Encryption data missing.");
        }
        if (empty($_SESSION['rsa_private_key'])) sendError("Session expired (Key missing). Please refresh.");

        $encryptedKey = base64_decode($encryptedKeyB64);
        $privateKey = openssl_pkey_get_private($_SESSION['rsa_private_key']);
        $decryptedAesKey = null;
        if (!openssl_private_decrypt($encryptedKey, $decryptedAesKey, $privateKey, OPENSSL_PKCS1_OAEP_PADDING)) {
             sendError("Secure handshake failed. Please retry.");
        }

        $encryptedDataWithTag = base64_decode($encryptedDataB64);
        $iv = base64_decode($ivB64);
        $tagLength = 16;
        $ciphertextLength = strlen($encryptedDataWithTag) - $tagLength;
        $ciphertext = substr($encryptedDataWithTag, 0, $ciphertextLength);
        $tag = substr($encryptedDataWithTag, -$tagLength);

        $jsonPayload = openssl_decrypt($ciphertext, 'aes-256-gcm', $decryptedAesKey, OPENSSL_RAW_DATA, $iv, $tag);
        if ($jsonPayload === false) sendError("Data decryption failed.");

        $paymentData = json_decode($jsonPayload, true);
        if (!$paymentData) sendError("Invalid decrypted payload.");

        $_POST['pickup_time'] = $paymentData['pickup_time'] ?? '';
        $_POST['token'] = $paymentData['token'] ?? '';
        $_POST['payment_method'] = 'card';
        
        $this->checkoutLegacy();
    }

    private function addToCart() {
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        if ($productId <= 0) sendError("Invalid product ID.");
        if ($quantity <= 0) sendError("Invalid quantity.");
        
        if (isset($_SESSION['cart'][$productId])) $_SESSION['cart'][$productId] += $quantity;
        else $_SESSION['cart'][$productId] = $quantity;

        sendSuccess(['message' => 'Product added to cart.', 'total_items' => array_sum($_SESSION['cart'])]);
    }

    private function removeFromCart() {
        $productId = intval($_POST['product_id'] ?? 0);
        if (isset($_SESSION['cart'][$productId])) unset($_SESSION['cart'][$productId]);
        sendSuccess(['message' => 'Item removed.', 'total_items' => array_sum($_SESSION['cart'])]);
    }

    private function updateQuantity() {
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);
        if ($quantity <= 0) unset($_SESSION['cart'][$productId]);
        else $_SESSION['cart'][$productId] = $quantity;
        sendSuccess(['message' => 'Cart updated.', 'total_items' => array_sum($_SESSION['cart'])]);
    }

    private function clearCart() {
        $_SESSION['cart'] = [];
        sendSuccess(['message' => 'Cart cleared.']);
    }

    private function validate_checkout() {
        ini_set('display_errors', '0'); error_reporting(E_ALL);
        if (empty($_SESSION['cart'])) sendError("Cart is empty.");
        $token = $_POST['token'] ?? '';
        $pickupTimeStr = $_POST['pickup_time'] ?? '';
        $userId = $_SESSION['user_id'] ?? null;
        
        $validation = $this->performValidation($token, $pickupTimeStr, $userId);
        sendSuccess(['message' => 'Validation successful', 'tableId' => $validation['tableId']]);
    }

    private function performValidation($token, $pickupTimeStr, $userId) {
        $isTableOrder = false;
        $tableId = null;
        $formattedTime = null;

        if (!empty($token)) {
            $decoded = base64_decode($token);
            if (strpos($decoded, 'Table ') === 0) {
                $isTableOrder = true;
                $parts = explode(' ', $decoded);
                if (isset($parts[1])) $tableId = intval($parts[1]);
                $now = new DateTime();
                $formattedTime = $now->format('Y-m-d H:i:s');
            }
        }

        if (!$userId && !$isTableOrder) sendError("You must be logged in to checkout.");

        if (!$isTableOrder) {
            if (empty($pickupTimeStr)) sendError("Pickup time is required.");
            try {
                $pickupTime = new DateTime($pickupTimeStr);
                $now = new DateTime();
                if ($pickupTime < $now) sendError("Orders cannot be placed in the past.");
                
                $buffer = clone $now; $buffer->modify('+15 minutes');
                if ($pickupTime < $buffer) sendError("Pickup time must be at least 15 minutes from now.");
                
                $dayOfWeek = (int)$pickupTime->format('w');
                $stmtS = $this->conn->prepare("SELECT open_time, close_time, is_closed FROM schedule WHERE day_of_week = ?");
                $stmtS->bind_param("i", $dayOfWeek);
                $stmtS->execute();
                $schedule = $stmtS->get_result()->fetch_assoc();
                
                if (!$schedule) {
                     if ($dayOfWeek === 0) $schedule = ['is_closed' => 1];
                     elseif ($dayOfWeek === 6) $schedule = ['is_closed' => 0, 'open_time' => '08:00:00', 'close_time' => '17:00:00'];
                     else $schedule = ['is_closed' => 0, 'open_time' => '07:00:00', 'close_time' => '17:00:00'];
                }
                
                if ($schedule['is_closed']) sendError("We are closed on " . $pickupTime->format('l') . "s.");
                
                $openTime = new DateTime($pickupTime->format('Y-m-d') . ' ' . $schedule['open_time']);
                $closeTime = new DateTime($pickupTime->format('Y-m-d') . ' ' . $schedule['close_time']);
                $lastPickup = clone $closeTime; $lastPickup->modify('-15 minutes');
                
                if ($pickupTime < $openTime || $pickupTime > $lastPickup) sendError("Pickup available between " . $openTime->format('H:i') . " and " . $lastPickup->format('H:i') . ".");
                
                $formattedTime = $pickupTime->format('Y-m-d H:i:s');
            } catch (Throwable $e) {
                sendError("Schedule check failed.");
            }
        }
        return ['isTableOrder' => $isTableOrder, 'tableId' => $tableId, 'formattedTime' => $formattedTime];
    }

    // ... (existing checkoutEncrypted, addToCart, etc) ...

    private function checkoutLegacy() {
        // ... (existing warnings suppression) ...
        ini_set('display_errors', '0'); 
        error_reporting(E_ALL);

        $pickupTimeStr = $_POST['pickup_time'] ?? '';
        $token = $_POST['token'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? 'card'; 
        
        $userId = $_SESSION['user_id'] ?? null;
        
        if (empty($_SESSION['cart'])) {
            sendError("Cart is empty.");
        }
        
        $valData = $this->performValidation($token, $pickupTimeStr, $userId);
        $isTableOrder = $valData['isTableOrder'];
        $tableId = $valData['tableId'];
        $formattedTime = $valData['formattedTime'];

        if (!$userId) {
            if ($isTableOrder) {
                $userId = $this->getOrCreateGuestUser();
            } else {
                sendError("You must be logged in to checkout.");
            }
        }

        // --- POINTS CALCULATION ---
        $pointsToUse = $_SESSION['cart_points'] ?? 0;
        if ($pointsToUse > 0) {
            // Verify balance again
            $currentPoints = $this->getUserPoints($userId);
            if ($pointsToUse > $currentPoints) {
                $pointsToUse = 0; // Reset if invalid
                $_SESSION['cart_points'] = 0;
            }
        }
        
        $loyaltyConfig = $this->getLoyaltyConfig();
        $pointsUnit = $loyaltyConfig['loyalty_spend_unit_points'];
        $valueUnit = $loyaltyConfig['loyalty_spend_unit_value'];
        
        $discountAmount = 0;
        if ($pointsToUse > 0 && $pointsUnit > 0) {
            $discountAmount = ($pointsToUse / $pointsUnit) * $valueUnit;
        }

        // ... Calculate Items Total ...
        $ids = array_keys($_SESSION['cart']);
        $idsString = implode(',', array_map('intval', $ids));
        $sql = "SELECT id, price, discount FROM products WHERE id IN ($idsString)";
        $result = $this->conn->query($sql);

        $orderItems = [];
        $itemsTotal = 0;

        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $qty = $_SESSION['cart'][$id];
            $price = floatval($row['price']);
            $discount = intval($row['discount'] ?? 0);
            
            $effectivePrice = $price;
            if ($discount > 0) $effectivePrice = $price - ($price * $discount / 100);
            
            $itemsTotal += ($effectivePrice * $qty);
            $orderItems[] = ['product_id' => $id, 'quantity' => $qty, 'price' => $effectivePrice];
        }
        
        // APPLY LOYALTY DISCOUNT
        $finalTotal = max(0, $itemsTotal - $discountAmount);

        // Transaction
        $this->conn->begin_transaction();

        try {
            // Updated INSERT to include points_spent
            $stmt = $this->conn->prepare("INSERT INTO orders (user_id, pickup_time, total_price, status, table_id, payment_method, points_spent) VALUES (?, ?, ?, 'pending', ?, ?, ?)");
            $stmt->bind_param("isdisi", $userId, $formattedTime, $finalTotal, $tableId, $paymentMethod, $pointsToUse);
            
            if (!$stmt->execute()) {
                throw new Exception("Order creation failed: " . $stmt->error);
            }
            $orderId = $stmt->insert_id;
            $stmt->close();
            
            // DEDUCT POINTS
            if ($pointsToUse > 0) {
                $updU = $this->conn->prepare("UPDATE users SET PuncteFidelitate = PuncteFidelitate - ? WHERE id = ?");
                $updU->bind_param("ii", $pointsToUse, $userId);
                if (!$updU->execute()) throw new Exception("Failed to deduct points.");
                $updU->close();
            }
            
            if ($tableId) {
                $updT = $this->conn->prepare("UPDATE tables SET Status='Ocupata' WHERE ID=?");
                $updT->bind_param("i", $tableId);
                $updT->execute();
            }

            // Insert Items
            $stmtItems = $this->conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
            foreach ($orderItems as $item) {
                $stmtItems->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
                if (!$stmtItems->execute()) throw new Exception("Order item creation failed.");
            }
            $stmtItems->close();

            $this->conn->commit();
            
            $_SESSION['cart'] = [];
            $_SESSION['cart_points'] = 0; // Clear points usage
            
            $msg = $isTableOrder ? "Order sent to kitchen (Table $tableId)." : "Order placed successfully!";
            sendSuccess(['message' => $msg, 'order_id' => $orderId]);

        } catch (Throwable $e) {
            $this->conn->rollback();
            sendError($e->getMessage());
        }
    }

    private function getCart() {
        if (empty($_SESSION['cart'])) {
            sendSuccess(['items' => [], 'total' => 0, 'subtotal' => 0, 'discount_total' => 0, 'tva_amount' => 0, 'tva_rate' => 0]);
        }

        $ids = array_keys($_SESSION['cart']);
        if (empty($ids)) {
             sendSuccess(['items' => [], 'total' => 0, 'subtotal' => 0, 'discount_total' => 0, 'tva_amount' => 0, 'tva_rate' => 0]);
        }

        $idsString = implode(',', array_map('intval', $ids));
        
        $sql = "SELECT id, name, price, discount, image_path, tva_code FROM products WHERE id IN ($idsString)";
        $result = $this->conn->query($sql);

        $items = [];
        $originalSubtotal = 0;
        $itemsTotal = 0;
        $tvaTotal = 0;
        $taxBreakdown = [];

        // Fetch TVA Settings
        $tvaRates = ['A' => 19, 'B' => 9, 'C' => 5, 'D' => 0];
        $resSettings = $this->conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'tva_%'");
        if ($resSettings) {
            while ($row = $resSettings->fetch_assoc()) {
                $k = strtoupper(substr($row['setting_key'], -1)); // tva_a -> A
                if (isset($tvaRates[$k])) {
                    $tvaRates[$k] = floatval($row['setting_value']);
                }
            }
        }

        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $qty = $_SESSION['cart'][$id];
            $price = floatval($row['price']);
            $discount = intval($row['discount'] ?? 0);
            $tvaCode = strtoupper($row['tva_code'] ?? 'A');
            $tvaRate = $tvaRates[$tvaCode] ?? 0;
            
            $effectivePrice = $price;
            if ($discount > 0) {
                $effectivePrice = $price - ($price * $discount / 100);
            }

            $lineOriginal = $price * $qty;
            $lineFinal = $effectivePrice * $qty;
            
            // Calculate TVA (Inclusive)
            // TVA = Total * Rate / (100 + Rate)
            $taxAmount = $lineFinal * $tvaRate / (100 + $tvaRate);
            
            $tvaTotal += $taxAmount;

            if (!isset($taxBreakdown[$tvaCode])) {
                $taxBreakdown[$tvaCode] = ['rate' => $tvaRate, 'amount' => 0, 'net' => 0];
            }
            $taxBreakdown[$tvaCode]['amount'] += $taxAmount;
            $taxBreakdown[$tvaCode]['net'] += ($lineFinal - $taxAmount);

            $row['quantity'] = $qty;
            $row['original_price'] = $price;
            $row['effective_price'] = $effectivePrice;
            $row['line_total'] = $lineFinal; // Final price for line
            $row['tva_code'] = $tvaCode;
            
            $items[] = $row;
            $originalSubtotal += $lineOriginal;
            $itemsTotal += $lineFinal;
        }
        
        // --- LOYALTY CALCULATION ---
        $loyaltyConfig = $this->getLoyaltyConfig();
        $userId = $_SESSION['user_id'] ?? null;
        $userPoints = $userId ? $this->getUserPoints($userId) : 0;
        $pointsApplied = $_SESSION['cart_points'] ?? 0;
        
        // Validate applied points vs current balance
        if ($pointsApplied > $userPoints) {
            $pointsApplied = 0;
            $_SESSION['cart_points'] = 0;
        }
        
        $loyaltyDiscount = 0;
        if ($pointsApplied > 0) {
            $loyaltyDiscount = ($pointsApplied / $loyaltyConfig['loyalty_spend_unit_points']) * $loyaltyConfig['loyalty_spend_unit_value'];
        }
        
        $finalTotal = max(0, $itemsTotal - $loyaltyDiscount);

        sendSuccess([
            'items' => $items,
            'subtotal' => $originalSubtotal, 
            'discount_total' => $originalSubtotal - $itemsTotal, // Regular discount
            'loyalty_discount' => $loyaltyDiscount,
            'loyalty_points_applied' => $pointsApplied,
            'user_points' => $userPoints,
            'loyalty_config' => $loyaltyConfig,
            'total' => $finalTotal,
            'tva_amount' => number_format($tvaTotal, 2),
            'tva_rate' => 0, 
            'tax_breakdown' => $taxBreakdown
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
