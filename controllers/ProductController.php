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
        $ingredients = trim($_POST['ingredients'] ?? '');
        $quantity = trim($_POST['quantity'] ?? ''); // New field
        $price = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? 'coffee');

        if (empty($name) || $price < 0) {
            sendError("Name and valid Price are required.");
        }

        $imagePath = $this->handleUpload();
        
        $sql = "INSERT INTO products (name, description, ingredients, quantity, price, category, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
             sendError("Prepare failed (Add): " . $this->conn->error);
        }
        $stmt->bind_param("ssssdss", $name, $description, $ingredients, $quantity, $price, $category, $imagePath);

        if ($stmt->execute()) {
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
        $ingredients = trim($_POST['ingredients'] ?? '');
        $quantity = trim($_POST['quantity'] ?? ''); // New field
        $price = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? 'coffee');

        if (empty($name) || $price < 0) {
            sendError("Name and valid Price are required.");
        }

        // Check if image is uploaded
        $imagePath = $this->handleUpload();
        
        if ($imagePath) {
             $sql = "UPDATE products SET name=?, description=?, ingredients=?, quantity=?, price=?, category=?, image_path=? WHERE id=?";
             $stmt = $this->conn->prepare($sql);
             if (!$stmt) {
                sendError("Prepare failed (Update Img): " . $this->conn->error);
             }
             $stmt->bind_param("ssssdssi", $name, $description, $ingredients, $quantity, $price, $category, $imagePath, $id);
        } else {
             $sql = "UPDATE products SET name=?, description=?, ingredients=?, quantity=?, price=?, category=? WHERE id=?";
             $stmt = $this->conn->prepare($sql);
             if (!$stmt) {
                sendError("Prepare failed (Update NoImg): " . $this->conn->error);
             }
             $stmt->bind_param("ssssdsi", $name, $description, $ingredients, $quantity, $price, $category, $id);
        }

        if ($stmt->execute()) {
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
