<?php 
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/csrf.php';
require_admin();
?>
<input type="hidden" id="csrf-token-global" value="<?= csrf_token() ?>">
<link rel="stylesheet" href="assets/css/admin.css?v=<?= time(); ?>">

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="assets/img/Logo Modificat.png" alt="Mazi Admin" style="max-width: 150px; margin-bottom: 10px;">
            <h3>Mazi Admin</h3>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-link active" data-section="dashboard">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="#" class="nav-link" data-section="orders">
                <i class="fas fa-receipt"></i> Running Orders
            </a>
            <a href="#" class="nav-link" data-section="menu">
                <i class="fas fa-coffee"></i> Menu Management
            </a>
            <a href="#" class="nav-link" data-section="slider">
                <i class="fas fa-images"></i> Slider Settings
            </a>
            <a href="#" class="nav-link" data-section="tables">
                <i class="fas fa-chair"></i> Table Management
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="?page=home" class="nav-link return-tosite" data-page="home">
                <i class="fas fa-home"></i> Back to Site
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
        <!-- Dashboard Section -->
        <section id="section-dashboard" class="admin-section">
            <div class="header-actions">
                <h2>Dashboard Overview</h2>
            </div>
            <div class="dashboard-stats">
                <p>Welcome to the Mazi Coffee Admin Panel.</p>
                <!-- Add stats here later -->
            </div>
        </section>

        <!-- Running Orders Section -->
        <section id="section-orders" class="admin-section" style="display: none;">
            <div class="header-actions">
                <h2>Running Orders</h2>
            </div>
            <div class="placeholder-content">
                <p>Order management interface coming soon.</p>
            </div>
        </section>

        <!-- Menu Management Section -->
        <section id="section-menu" class="admin-section" style="display: none;">
            <div class="header-actions">
                <h2>Menu Management</h2>
                <button id="add-product-btn" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Add New Coffee
                </button>
            </div>

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
        </section>

        <!-- Slider Settings Section -->
        <section id="section-slider" class="admin-section" style="display: none;">
            <div class="header-actions">
                <h2>Slider Settings</h2>
                <button id="add-slide-btn" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Add New Slide
                </button>
            </div>
            <div class="slider-list" id="slider-list">
                <!-- Populated by JS -->
                 <p>Loading slider settings...</p>
            </div>
        </section>

        <!-- Table Management Section -->
        <section id="section-tables" class="admin-section" style="display: none;">
        <div class="header-actions">
                <h2>Table Management</h2>
                <div class="table-controls" style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-weight: bold; color: #2c3e50;">Total Tables:</span>
                    <div style="display: flex; align-items: center; gap: 10px; background: #fff; padding: 5px; border-radius: 8px; border: 1px solid #ddd;">
                        <button id="remove-table-btn" class="btn btn-danger btn-sm" style="padding: 5px 12px; margin: 0;"><i class="fas fa-minus"></i></button>
                        <span id="table-count-display" style="font-size: 1.2rem; font-weight: bold; min-width: 30px; text-align: center;">-</span>
                        <button id="add-table-btn" class="btn btn-secondary btn-sm" style="padding: 5px 12px; margin: 0;width: 40px;"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div style="margin-top: 10px; display: flex; gap: 10px;">
                    <button id="save-positions-btn" class="btn btn-success btn-sm">Save Positions</button>
                    <button id="upload-floor-plan-btn" class="btn btn-3 btn-sm"><i class="fas fa-image"></i> Upload Floor Plan</button>
                    <input type="file" id="floor-plan-upload" style="display: none;" accept="image/*">
                </div>
            </div>
            
            <div id="floor-plan-container" class="floor-plan" style="position: relative; width: 100%; max-width: 800px; height: 600px; margin: 0 auto 20px auto; background: #e0e0e0; border: 2px solid #ccc; border-radius: 8px; overflow: hidden; background-image: radial-gradient(#ccc 1px, transparent 1px); background-size: 100% 100%; background-repeat: no-repeat; background-position: center;">
                <!-- Draggable Tables will be here -->
                <p style="position: absolute; top: 10px; left: 10px; z-index:0; color: #888; pointer-events: none;">Floor Plan Area</p>
            </div>

            <!-- Table Properties Modal -->
            <div id="table-props-modal" class="modal">
                <div class="modal-content" style="max-width: 400px;">
                    <span class="close-modal" data-target="table-props-modal">&times;</span>
                    <h2>Table Properties</h2>
                    <form id="table-props-form">
                        <input type="hidden" id="prop-table-id">
                        <div class="form-group">
                            <label>Shape</label>
                            <select id="prop-shape" class="form-control">
                                <option value="circle">Circle (Round)</option>
                                <option value="square">Square</option>
                                <option value="rectangle">Rectangle</option>
                            </select>
                        </div>
                        <div class="form-group" style="display: flex; gap: 10px;">
                            <div style="flex:1">
                                <label>Width (px)</label>
                                <input type="number" id="prop-width" class="form-control" min="20" max="300">
                            </div>
                            <div style="flex:1">
                                <label>Height (px)</label>
                                <input type="number" id="prop-height" class="form-control" min="20" max="300">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Update Table</button>
                    </form>
                </div>
            </div>

            <div id="tables-grid" class="tables-grid" style="display: flex; flex-wrap: wrap; gap: 10px; border-top: 1px solid #ddd; padding-top: 20px;">

            <div id="tables-grid" class="tables-grid" style="display: flex; flex-wrap: wrap; gap: 10px; border-top: 1px solid #ddd; padding-top: 20px;">
                <!-- List view still useful for status updates -->
                <p>Detailed List:</p>
            </div>
        </section>
    </main>
</div>

<!-- Add/Edit Modal -->
<div id="product-modal" class="modal">
    <!-- Existing Product Modal Content -->
    <div class="modal-content">
        <span class="close-modal" data-target="product-modal">&times;</span>
        <h3 id="modal-title">Add Product</h3>
        <form id="product-form" enctype="multipart/form-data">
            <input type="hidden" name="id" id="prod-id">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
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

<!-- Slider Modal -->
<div id="slider-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal" data-target="slider-modal">&times;</span>
        <h3>Add New Slide</h3>
        <form id="slider-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="entity" value="slider">
            
            <div class="form-group">
                <label for="slide-title">Title (Optional)</label>
                <input type="text" id="slide-title" name="title" placeholder="e.g. Welcome to Mazi">
            </div>

            <div class="form-group">
                <label for="slide-subtitle">Subtitle (Optional)</label>
                <input type="text" id="slide-subtitle" name="subtitle" placeholder="e.g. Best Coffee in Town">
            </div>

            <div class="form-group">
                <label for="slide-image">Image (Required)</label>
                <input type="file" id="slide-image" name="image" accept="image/*" required>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                <input type="checkbox" id="slide-btn-visible" name="is_button_visible" value="1" checked onchange="document.getElementById('slide-btn-options').style.display = this.checked ? 'block' : 'none'">
                <label for="slide-btn-visible" style="margin: 0;">Show Call-to-Action Button</label>
            </div>

            <div id="slide-btn-options">
                <div class="form-group">
                    <label for="slide-btn-text">Button Text</label>
                    <input type="text" id="slide-btn-text" name="button_text" value="View Menu" placeholder="e.g. Order Now">
                </div>
                <div class="form-group">
                    <label for="slide-btn-link">Button Link</label>
                    <input type="text" id="slide-btn-link" name="button_link" value="?page=menu" placeholder="e.g. ?page=tables">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Add Slide</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/admin.js?v=<?= time(); ?>"></script>
