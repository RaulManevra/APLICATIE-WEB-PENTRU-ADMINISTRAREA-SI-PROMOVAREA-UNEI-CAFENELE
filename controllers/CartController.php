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
            default:
                sendError("Invalid cart action.");
        }
    }

    /**
     * Generates or retrieves an RSA Key Pair for the session.
     * Returns the Public Key to the client for encryption.
     */
    private function getPublicKey() {
        if (!isset($_SESSION['rsa_private_key'])) {
            $config = array(
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            );
            
            // Only add specific config path for Windows, rely on defaults for others (tested on macOS)
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $config["config"] = "d:\\Apps\\Ampps\\apache\\conf\\openssl.cnf";
            }
            
            // Create the private and public key
            $res = openssl_pkey_new($config);
            if (!$res) {
                sendError("Encryption setup failed: " . openssl_error_string());
            }

            // Extract the private key
            $exportSuccess = openssl_pkey_export($res, $privateKey, null, $config);
            
            if (!$exportSuccess) {
                 $err = openssl_error_string();
                 file_put_contents(__DIR__ . '/../ssl_debug.log', "Export Failed: $err\nConfig: " . print_r($config, true));
                 sendError("Encryption export failed: " . $err);
            }
            
            $_SESSION['rsa_private_key'] = $privateKey;

            // Extract the public key
            $keyDetails = openssl_pkey_get_details($res);
            $publicKey = $keyDetails['key'];
            $_SESSION['rsa_public_key'] = $publicKey; // Optional, mostly for debug
        }

        sendSuccess(['publicKey' => $_SESSION['rsa_public_key']]);
    }

    /**
     * Handles the checkout process with encrypted payload.
     * Use this method to process secure payments.
     */
    private function checkoutEncrypted() {
        // Fix for "Network Error": Suppress HTML output errors
        ini_set('display_errors', '0'); 
        error_reporting(E_ALL);

        // 1. Decryption Phase
        $encryptedKeyB64 = $_POST['encrypted_key'] ?? '';
        $encryptedDataB64 = $_POST['encrypted_data'] ?? '';
        $ivB64 = $_POST['iv'] ?? '';

        if (empty($encryptedKeyB64) || empty($encryptedDataB64) || empty($ivB64)) {
            // Fallback to legacy unencrypted checkout if no encryption params check (Optional)
            // For now, enforce encryption
            if (isset($_POST['payment_method']) && !isset($_POST['encrypted_key'])) {
                $this->checkoutLegacy(); 
                return;
            }
            sendError("Encryption data missing.");
        }

        if (empty($_SESSION['rsa_private_key'])) {
            sendError("Session expired (Key missing). Please refresh.");
        }

        // A. Decrypt the Session Key (AES Key) using RSA Private Key
        $encryptedKey = base64_decode($encryptedKeyB64);
        $privateKey = openssl_pkey_get_private($_SESSION['rsa_private_key']);
        $decryptedAesKey = null;

        if (!openssl_private_decrypt($encryptedKey, $decryptedAesKey, $privateKey, OPENSSL_PKCS1_OAEP_PADDING)) {
             $err = openssl_error_string();
             file_put_contents(__DIR__ . '/../ssl_debug.log', "Decryption Failed: $err\nKey len: " . strlen($encryptedKey) . "\n", FILE_APPEND);
             sendError("Secure handshake failed. Please retry.");
        }

        // B. Decrypt the Data Payload using AES-GCM
        // PHP built-in openssl_decrypt for GCM requires PHP 7.1+ and the tag.
        // The WebCrypto API usually appends the tag to the ciphertext or sends it separately.
        // We will assume the client sends: IV + Ciphertext + Tag (Standard concatenation) 
        // OR we can ask client to send tag separately.
        // Let's assume the standard: Ciphertext = EncryptedBody (variable) + AuthTag (16 bytes)
        
        $encryptedDataWithTag = base64_decode($encryptedDataB64);
        $iv = base64_decode($ivB64);
        
        // Extract Tag (Last 16 bytes)
        $tagLength = 16;
        $ciphertextLength = strlen($encryptedDataWithTag) - $tagLength;
        $ciphertext = substr($encryptedDataWithTag, 0, $ciphertextLength);
        $tag = substr($encryptedDataWithTag, -$tagLength);

        // Decrypt
        $jsonPayload = openssl_decrypt($ciphertext, 'aes-256-gcm', $decryptedAesKey, OPENSSL_RAW_DATA, $iv, $tag);
        
        if ($jsonPayload === false) {
             sendError("Data decryption failed.");
        }

        $paymentData = json_decode($jsonPayload, true);
        if (!$paymentData) {
            sendError("Invalid decrypted payload.");
        }

        // 2. Map Decrypted Data to Request Simulation
        // For the rest of the logic to work, we simulate that specific POST vars are present
        $_POST['pickup_time'] = $paymentData['pickup_time'] ?? '';
        $_POST['token'] = $paymentData['token'] ?? '';
        $_POST['payment_method'] = 'card'; // logic hardcodet for this flow
        
        // Log the success (simulating a real payment gateway log)
        // Store last 4 digits only, NEVER full card
        $last4 = substr($paymentData['cardNumber'] ?? '0000', -4);
        error_log("Payment Processed: Card ending in $last4");

        // 3. Proceed with standard checkout logic
        $this->checkoutLegacy();
    }

    /**
     * Original checkout logic, renamed to support internal call
     */
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

    private function validate_checkout() {
        // Fix for "Network Error": Suppress HTML output errors that break JSON
        ini_set('display_errors', '0'); 
        error_reporting(E_ALL);

        $pickupTimeStr = $_POST['pickup_time'] ?? '';
        $token = $_POST['token'] ?? '';
        
        $userId = $_SESSION['user_id'] ?? null;
        
        // 0. CHECK CART (First Priority)
        if (empty($_SESSION['cart'])) {
            sendError("Cart is empty.");
        }

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
            } catch (Exception $e) {
                sendError("Invalid date format.");
            }

            try {
                $now = new DateTime();

                if ($pickupTime < $now) {
                    sendError("Orders cannot be placed in the past.");
                }
                
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
                
            } catch (Throwable $e) {
                sendError("Schedule check failed. Please try again or contact us.");
            }
        }
        
        return ['isTableOrder' => $isTableOrder, 'tableId' => $tableId, 'formattedTime' => $formattedTime];
    }

    private function checkoutLegacy() {
        // Fix for "Network Error": Suppress HTML output errors that break JSON
        ini_set('display_errors', '0'); 
        error_reporting(E_ALL);

        $pickupTimeStr = $_POST['pickup_time'] ?? '';
        $token = $_POST['token'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? 'card'; // Default to card for legacy/web
        
        $userId = $_SESSION['user_id'] ?? null;
        
        // 0. CHECK CART (First Priority)
        if (empty($_SESSION['cart'])) {
            sendError("Cart is empty.");
        }
        
        // Re-run validation to be safe (incase direct call)
        $valData = $this->performValidation($token, $pickupTimeStr, $userId);
        
        $isTableOrder = $valData['isTableOrder'];
        $tableId = $valData['tableId'];
        $formattedTime = $valData['formattedTime'];

        // --- AUTH CHECK ---
        if (!$userId) {
            if ($isTableOrder) {
                // Allow Guest Checkout for Table Orders
                $userId = $this->getOrCreateGuestUser();
            } else {
                sendError("You must be logged in to checkout.");
            }
        }

        // Calculate total and prepare items
        $ids = array_keys($_SESSION['cart']);
        $idsString = implode(',', array_map('intval', $ids));
        $sql = "SELECT id, price, discount FROM products WHERE id IN ($idsString)";
        $result = $this->conn->query($sql);

        $orderItems = [];
        $totalPrice = 0;

        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $qty = $_SESSION['cart'][$id];
            $price = floatval($row['price']);
            $discount = intval($row['discount'] ?? 0);
            
            $effectivePrice = $price;
            if ($discount > 0) {
                $effectivePrice = $price - ($price * $discount / 100);
            }
            
            $subtotal = $effectivePrice * $qty;
            
            $totalPrice += $subtotal;
            $orderItems[] = [
                'product_id' => $id,
                'quantity' => $qty,
                'price' => $effectivePrice // Save the discounted price at time of order
            ];
        }

        // Transaction
        $this->conn->begin_transaction();

        try {
            // Updated INSERT to include table_id and payment_method
            $stmt = $this->conn->prepare("INSERT INTO orders (user_id, pickup_time, total_price, status, table_id, payment_method) VALUES (?, ?, ?, 'pending', ?, ?)");
            $stmt->bind_param("isdis", $userId, $formattedTime, $totalPrice, $tableId, $paymentMethod);
            
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
        $finalTotal = 0;
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
            $finalTotal += $lineFinal;
        }

        sendSuccess([
            'items' => $items,
            'subtotal' => $originalSubtotal, 
            'discount_total' => $originalSubtotal - $finalTotal,
            'total' => $finalTotal,
            'tva_amount' => number_format($tvaTotal, 2),
            'tva_rate' => 0, // Deprecated single rate
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
