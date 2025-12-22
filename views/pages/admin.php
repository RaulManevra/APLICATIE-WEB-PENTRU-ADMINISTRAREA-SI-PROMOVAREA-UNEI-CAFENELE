<?php 
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}

require_once __DIR__ . '/../../core/auth.php';
require_admin();
?>
<link rel="stylesheet" href="assets/css/admin.css?v=<?= time(); ?>">

<div class="admin-dashboard">
    <div class="header-actions">
        <h2>Menu Management <small style="font-size:0.5em; color:#888;">(Admin Panel)</small></h2>
        <button id="add-product-btn" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Coffee
        </button>
    </div>

    <!-- Stats/Overview (Optional for now) -->
    
    <!-- Products Table -->
    <div class="table-container">
        <table class="data-table" id="products-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price (RON)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Populated by JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="product-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3 id="modal-title">Add Product</h3>
        <form id="product-form" enctype="multipart/form-data">
            <input type="hidden" name="id" id="prod-id">
            <input type="hidden" name="action" id="form-action" value="add">
            
            <div class="form-group">
                <label for="prod-name">Name</label>
                <input type="text" id="prod-name" name="name" required>
            </div>

            <div class="form-group">
                <label for="prod-price">Price (RON)</label>
                <input type="number" id="prod-price" name="price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="prod-category">Category</label>
                <select id="prod-category" name="category">
                    <option value="coffee">Coffee</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="prod-desc">Description</label>
                <textarea id="prod-desc" name="description" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="prod-image">Image</label>
                <input type="file" id="prod-image" name="image" accept="image/*">
                <div id="current-image-preview" style="margin-top: 10px; display: none;">
                    <p>Current:</p>
                    <img src="" id="preview-img" style="max-height: 100px;">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/js/admin.js?v=<?= time(); ?>"></script>
