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
                sendError("Invalid action.");
        }
    }

    private function getAll() {
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        sendSuccess($products);
    }

    private function add() {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = $_POST['category'] ?? 'coffee';

        if ($name === '' || $price <= 0) {
            sendError("Name and valid price are required.");
        }

        $imagePath = $this->handleUpload();
        if (!$imagePath) {
             // Use a default or require image? Let's require it for now or use a placeholder
             // For this app, let's require it or set null if allowed, but schema allows null.
             // We'll proceed with NULL if no image, but usually menu items need images.
             // Let's assume default for now to be safe or just NULL.
             $imagePath = 'assets/menu/images/default_coffee.jpg'; 
        }

        $stmt = $this->conn->prepare("INSERT INTO products (name, description, price, image_path, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $name, $desc, $price, $imagePath, $category);
        
        if ($stmt->execute()) {
            sendSuccess(['id' => $stmt->insert_id, 'message' => 'Product added successfully.']);
        } else {
            sendError("Failed to add product: " . $stmt->error);
        }
    }

    private function update() {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) sendError("Invalid Product ID");

        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = $_POST['category'] ?? 'coffee';

        if ($name === '' || $price <= 0) {
            sendError("Name and valid price are required.");
        }

        // Check if new image uploaded
        $imagePath = $this->handleUpload();
        
        if ($imagePath) {
            $stmt = $this->conn->prepare("UPDATE products SET name=?, description=?, price=?, image_path=?, category=? WHERE id=?");
            $stmt->bind_param("ssdssi", $name, $desc, $price, $imagePath, $category, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE products SET name=?, description=?, price=?, category=? WHERE id=?");
            $stmt->bind_param("ssdsi", $name, $desc, $price, $category, $id);
        }

        if ($stmt->execute()) {
            sendSuccess(['message' => 'Product updated successfully.']);
        } else {
            sendError("Failed to update product.");
        }
    }

    private function delete() {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) sendError("Invalid Product ID");

        // Optional: Delete image file from server too.
        // First get the image path
        $stmt = $this->conn->prepare("SELECT image_path FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $file = __DIR__ . '/../' . $row['image_path'];
            if (file_exists($file) && !str_contains($file, 'default')) {
                // @unlink($file); // Commented out for safety unless requested, but usually desired.
            }
        }

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

        // Ensure directory exists
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
