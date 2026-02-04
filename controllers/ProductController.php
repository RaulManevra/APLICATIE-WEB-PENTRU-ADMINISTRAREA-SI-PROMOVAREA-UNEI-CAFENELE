<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/output.php';

class ProductController {
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
            case 'add':
                $this->add();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'get_linked_ingredients':
                $this->getLinkedIngredients();
                break;
            default:
                sendError("Invalid action for product: " . $action);
        }
    }

    private function getAll() {
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        sendSuccess(['data' => $products]);
    }

    private function add() {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $quantity = trim($_POST['quantity'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $discount = intval($_POST['discount'] ?? 0);
        $tvaCode = $_POST['tva_code'] ?? 'A';
        if (!in_array($tvaCode, ['A', 'B', 'C', 'D'])) $tvaCode = 'A';

        $category = trim($_POST['category'] ?? 'coffee');

        if (empty($name) || $price < 0) {
            sendError("Name and valid Price are required.");
        }
        if ($discount < 0 || $discount > 100) {
            sendError("Discount must be between 0 and 100.");
        }

        $imagePath = $this->handleUpload();
        
        $tags = trim($_POST['tags'] ?? '');

        $sql = "INSERT INTO products (name, description, quantity, price, discount, tva_code, category, image_path, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
             sendError("Prepare failed (Add): " . $this->conn->error);
        }
        $stmt->bind_param("sssdissss", $name, $description, $quantity, $price, $discount, $tvaCode, $category, $imagePath, $tags);

        if ($stmt->execute()) {
            $lastId = $this->conn->insert_id; // Get inserted ID
            // Handle Linked Ingredients
            if (isset($_POST['linked_ingredients'])) {
                $this->saveLinkedIngredients($lastId, $_POST['linked_ingredients']);
            }
            sendSuccess(['message' => 'Product added successfully.']);
        } else {
            sendError("Failed to add product: " . $stmt->error);
        }
    }

    private function update() {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) sendError("Invalid Product ID");

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $quantity = trim($_POST['quantity'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $discount = intval($_POST['discount'] ?? 0); // New field
        $category = trim($_POST['category'] ?? 'coffee');

        if (empty($name) || $price < 0) {
            sendError("Name and valid Price are required.");
        }
        if ($discount < 0 || $discount > 100) {
            sendError("Discount must be between 0 and 100.");
        }

        $tvaCode = $_POST['tva_code'] ?? 'A';
        if (!in_array($tvaCode, ['A', 'B', 'C', 'D'])) $tvaCode = 'A';

        // Check if image is uploaded
        $imagePath = $this->handleUpload();
        
        $tags = trim($_POST['tags'] ?? '');
        
        if ($imagePath) {
             $sql = "UPDATE products SET name=?, description=?, quantity=?, price=?, discount=?, tva_code=?, category=?, image_path=?, tags=? WHERE id=?";
             $stmt = $this->conn->prepare($sql);
             if (!$stmt) {
                sendError("Prepare failed (Update Img): " . $this->conn->error);
             }
             $stmt->bind_param("sssdissssi", $name, $description, $quantity, $price, $discount, $tvaCode, $category, $imagePath, $tags, $id);
        } else {
             $sql = "UPDATE products SET name=?, description=?, quantity=?, price=?, discount=?, tva_code=?, category=?, tags=? WHERE id=?";
             $stmt = $this->conn->prepare($sql);
             if (!$stmt) {
                sendError("Prepare failed (Update NoImg): " . $this->conn->error);
             }
             $stmt->bind_param("sssdisssi", $name, $description, $quantity, $price, $discount, $tvaCode, $category, $tags, $id);
        }

        if ($stmt->execute()) {
             // Handle Linked Ingredients
            if (isset($_POST['linked_ingredients'])) {
                $this->saveLinkedIngredients($id, $_POST['linked_ingredients']);
            }
            sendSuccess(['message' => 'Product updated successfully.']);
        } else {
            sendError("Failed to update product: " . $stmt->error);
        }
    }

    private function delete() {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) sendError("Invalid Product ID");

        // Optional: Delete image file if exists
        // $stmt = $this->conn->prepare("SELECT image_path FROM products WHERE id=?");
        // ... (Similar to SliderController logic)

        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendSuccess(['message' => 'Product deleted successfully.']);
        } else {
            sendError("Failed to delete product.");
        }
    }

    private function getLinkedIngredients() {
        $pid = intval($_POST['product_id'] ?? 0);
        if ($pid <= 0) sendError("Invalid Product ID");

        $sql = "SELECT pi.*, i.name, i.type, i.stock_amount as current_stock 
                FROM product_ingredients pi 
                JOIN ingredients i ON pi.ingredient_id = i.id 
                WHERE pi.product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        sendSuccess(['data' => $data]);
    }

    private function saveLinkedIngredients($productId, $json) {
        $items = json_decode($json, true);
        if (!is_array($items)) return;

        // Clear existing
        $del = $this->conn->prepare("DELETE FROM product_ingredients WHERE product_id = ?");
        $del->bind_param("i", $productId);
        $del->execute();

        if (empty($items)) return;

        $sql = "INSERT INTO product_ingredients (product_id, ingredient_id, quantity, unit) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        foreach ($items as $item) {
            $ingId = intval($item['id']);
            $rawQty = floatval($item['quantity']);
            $unit = trim($item['unit'] ?? '');
            
            // Convert to base unit (g/ml)
            // Note: We blindly convert assuming standard prefixes. 
            // If unit is just 'g' or 'ml' or 'kg' or 'l'.
            $baseQty = $this->convertToBase($rawQty, $unit);
            // We store the BASE unit as the unit text? Or keep original?
            // Better store the base unit symbol so we know it's normalized.
            // Ingredients table stores 'solid' -> g, 'liquid' -> ml implied.
            // But let's look at stored unit. 'g' or 'ml'.
            $baseUnit = (in_array(strtolower($unit), ['l', 'ml'])) ? 'ml' : 'g'; 
            
            // Actually, conversion depends:
            // if solid: kg -> *1000 -> g.
            // if liquid: l -> *1000 -> ml.
            
            if ($ingId > 0 && $baseQty > 0) {
               $stmt->bind_param("iids", $productId, $ingId, $baseQty, $baseUnit);
               $stmt->execute(); 
            }
        }
    }

    private function convertToBase($qty, $unit) {
        switch (strtolower($unit)) {
            case 'kg': return $qty * 1000;
            case 'mg': return $qty / 1000;
            case 'g': return $qty;
            case 'l': return $qty * 1000;
            case 'ml': return $qty;
            default: return $qty;
        }
    }

    private function handleUpload() {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['image']['tmp_name']);

        if (!in_array($mime, $allowedTypes)) {
            sendError("Invalid file type. Only JPG, PNG, WEBP allowed.");
        }

        // Directory: assets/menu/images/
        $uploadDir = __DIR__ . '/../assets/menu/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('prod_') . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        $dbPath = 'assets/menu/images/' . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            return $dbPath;
        }
        
        return null;
    }
}
