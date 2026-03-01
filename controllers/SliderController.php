<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/output.php';

class SliderController {
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
            case 'delete':
                $this->delete();
                break;
            case 'update':
                $this->update();
                break;
            case 'reorder':
                $this->reorder();
                break;
            default:
                sendError("Invalid action for slider.");
        }
    }

    private function getAll() {
        $sql = "SELECT * FROM slider_images ORDER BY display_order ASC";
        $result = $this->conn->query($sql);
        $slides = [];
        while ($row = $result->fetch_assoc()) {
            $slides[] = $row;
        }
        sendSuccess(['data' => $slides]);
    }

    private function add() {
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $btnText = trim($_POST['button_text'] ?? 'View Menu');
        $btnLink = trim($_POST['button_link'] ?? '?page=menu');
        $btnVisible = isset($_POST['is_button_visible']) ? 1 : 0;

        // Image is mandatory for a slider
        $imagePath = $this->handleUpload();
        if (!$imagePath) {
            sendError("Image is required for slider.");
        }
        
        $description = trim($_POST['description'] ?? '');

        $stmt = $this->conn->prepare("INSERT INTO slider_images (image_path, title, subtitle, description, button_text, button_link, is_button_visible) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $imagePath, $title, $subtitle, $description, $btnText, $btnLink, $btnVisible);
        
        if ($stmt->execute()) {
            sendSuccess(['id' => $stmt->insert_id, 'message' => 'Slide added successfully.']);
        } else {
            // cleanup if db fails
            if (file_exists(__DIR__ . '/../' . $imagePath)) {
                unlink(__DIR__ . '/../' . $imagePath);
            }
            sendError("Failed to add slide: " . $stmt->error);
        }
    }

    private function delete() {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) sendError("Invalid Slide ID");

        // Get path to delete file
        $stmt = $this->conn->prepare("SELECT image_path FROM slider_images WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $file = __DIR__ . '/../' . $row['image_path'];
            if (file_exists($file)) {
                unlink($file);
            }
        } else {
             sendError("Slide not found.");
        }

        $stmt = $this->conn->prepare("DELETE FROM slider_images WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendSuccess(['message' => 'Slide deleted successfully.']);
        } else {
            sendError("Failed to delete slide.");
        }
    }

    private function update() {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) sendError("Invalid Slide ID");

        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $btnText = trim($_POST['button_text'] ?? 'View Menu');
        $btnLink = trim($_POST['button_link'] ?? '?page=menu');
        $btnVisible = isset($_POST['is_button_visible']) ? 1 : 0;

        // Check for new image
        $imagePath = $this->handleUpload();
        
        if ($imagePath) {
            // Delete old image
            $stmt = $this->conn->prepare("SELECT image_path FROM slider_images WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $oldFile = __DIR__ . '/../' . $row['image_path'];
                if (file_exists($oldFile)) unlink($oldFile);
            }

            $stmt = $this->conn->prepare("UPDATE slider_images SET image_path=?, title=?, subtitle=?, description=?, button_text=?, button_link=?, is_button_visible=? WHERE id=?");
            $stmt->bind_param("ssssssii", $imagePath, $title, $subtitle, $description, $btnText, $btnLink, $btnVisible, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE slider_images SET title=?, subtitle=?, description=?, button_text=?, button_link=?, is_button_visible=? WHERE id=?");
            $stmt->bind_param("sssssii", $title, $subtitle, $description, $btnText, $btnLink, $btnVisible, $id);
        }

        if ($stmt->execute()) {
            sendSuccess(['message' => 'Slide updated successfully.']);
        } else {
            sendError("Failed to update slide: " . $stmt->error);
        }
    }

    private function reorder() {
        $order = $_POST['order'] ?? [];
        if (!is_array($order)) {
            sendError("Invalid order data.");
        }

        foreach ($order as $position => $id) {
            $id = intval($id);
            $pos = intval($position);
            $this->conn->query("UPDATE slider_images SET display_order = $pos WHERE id = $id");
        }
        sendSuccess(['message' => 'Order updated.']);
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
        $uploadDir = __DIR__ . '/../assets/images/slider/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('slide_') . '.' . $ext;
        $targetPath = $uploadDir . $filename;
        $dbPath = 'assets/images/slider/' . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            return $dbPath;
        }
        
        return null;
    }
}
