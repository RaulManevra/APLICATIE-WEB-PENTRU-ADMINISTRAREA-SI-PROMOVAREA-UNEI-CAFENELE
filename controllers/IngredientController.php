<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/output.php';
require_once __DIR__ . '/../core/SessionManager.php';

class IngredientController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function handleRequest() {
        if (!SessionManager::isLoggedIn()) {
            sendError("Unauthorized.");
        }

        // Only Admin or Employer can manage stock? 
        // Plan says admin stock page. Usually employer also manages stock.
        // Let's allow both for now as per base roles.
        
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
                sendError("Invalid ingredient action.");
        }
    }

    private function getAll() {
        $search = $_GET['search'] ?? '';
        $sql = "SELECT * FROM ingredients";
        
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $sql .= " WHERE name LIKE ?";
            $params[] = "%" . $search . "%";
            $types .= "s";
        }
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
             $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        
        $data = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                // Format stock amount for display
                $amount = (float)$row['stock_amount'];
                $type = $row['type'];
                $formatted = "";
                
                if ($type === 'solid') {
                    if ($amount >= 1000) {
                        $formatted = round($amount / 1000, 2) . " Kg";
                    } else {
                        $formatted = $amount . " g";
                    }
                } else {
                    if ($amount >= 1000) {
                        $formatted = round($amount / 1000, 2) . " L";
                    } else {
                        $formatted = $amount . " Ml";
                    }
                }
                
                $row['formatted_stock'] = $formatted;
                $data[] = $row;
            }
        }
        sendSuccess(['data' => $data]);
    }

    private function add() {
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? 'solid'; // solid, liquid
        $inputQty = (float)($_POST['quantity'] ?? 0);
        $unit = $_POST['unit'] ?? ($type === 'solid' ? 'g' : 'ml');
        
        if (empty($name)) sendError("Name is required");
        
        // Convert input quantity to base unit (g or ml)
        $stockAmount = $this->convertToBase($inputQty, $unit);

        // Image Upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
             // Simple upload logic
             $uploadDir = __DIR__ . '/../assets/uploads/ingredients/';
             if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
             
             $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
             $filename = 'ing_' . uniqid() . '.' . $ext;
             $target = $uploadDir . $filename;
             
             if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                 $imagePath = 'assets/uploads/ingredients/' . $filename;
             }
        }

        $stmt = $this->conn->prepare("INSERT INTO ingredients (name, type, stock_amount, image_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $type, $stockAmount, $imagePath);
        
        if ($stmt->execute()) {
            sendSuccess(['message' => 'Ingredient added successfully']);
        } else {
            sendError("Failed to add ingredient: " . $this->conn->error);
        }
    }

    private function update() {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) sendError("Invalid ID");
        
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? 'solid';
        $inputQty = (float)($_POST['quantity'] ?? 0);
        $unit = $_POST['unit'] ?? '';

        // Check if we are updating stock amount directly or if we just want to update name/image
        // The modal usually sends all fields.
        
        // Fetch existing to get current values if fields missing?
        // Let's assume full update from modal for now.
        
        $stockAmount = $this->convertToBase($inputQty, $unit);
        
        // Image logic
        $imageClause = "";
        $params = ["ssdi", $name, $type, $stockAmount, $id];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
             $uploadDir = __DIR__ . '/../assets/uploads/ingredients/';
             if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
             $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
             $filename = 'ing_' . uniqid() . '.' . $ext;
             $target = $uploadDir . $filename;
             if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                 $imagePath = 'assets/uploads/ingredients/' . $filename;
                 $imageClause = ", image_path = ?";
                 $params = ["ssdsi", $name, $type, $stockAmount, $imagePath, $id];
             }
        }

        $sql = "UPDATE ingredients SET name = ?, type = ?, stock_amount = ? $imageClause WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(...$params);
        
        if ($stmt->execute()) {
             sendSuccess(['message' => 'Ingredient updated']);
        } else {
             sendError("Failed to update");
        }
    }

    private function delete() {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) sendError("Invalid ID");
        
        // Optional: Check if used in products?
        // Next phase.
        
        $stmt = $this->conn->prepare("DELETE FROM ingredients WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
             sendSuccess(['message' => 'Ingredient deleted']);
        } else {
             sendError("Failed to delete");
        }
    }

    private function convertToBase($qty, $unit) {
        // Base units: g, ml
        // Inputs: kg, g, mg | l, ml
        
        switch (strtolower($unit)) {
            case 'kg': return $qty * 1000;
            case 'mg': return $qty / 1000;
            case 'g': return $qty;
            
            case 'l': return $qty * 1000;
            case 'ml': return $qty;
            
            default: return $qty;
        }
    }
}
