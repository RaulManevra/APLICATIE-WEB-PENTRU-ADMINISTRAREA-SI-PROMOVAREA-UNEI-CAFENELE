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
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            <a href="#" class="nav-link" data-section="reservations">
                <i class="fas fa-calendar-alt"></i> Reservations
            </a>
            <a href="#" class="nav-link" data-section="menu">
                <i class="fas fa-coffee"></i> Menu Management
            </a>
            <a href="#" class="nav-link" data-section="tables">
                <i class="fas fa-chair"></i> Floor Plan
            </a>
             <a href="#" class="nav-link" data-section="users">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="#" class="nav-link" data-section="slider">
                <i class="fas fa-images"></i> Slider
            </a>
            <a href="#" class="nav-link" data-section="settings">
                <i class="fas fa-cogs"></i> Settings
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
            <div class="dashboard-header">
                <h2>Dashboard Overview</h2>
                <div class="cafe-status-toggle">
                    <span>Cafe Status:</span>
                    <select id="global-cafe-status" onchange="updateCafeStatus(this.value)">
                        <option value="open">Open</option>
                        <option value="busy">Busy</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e8f5e9; color: #2e7d32;"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Reservations (Today)</span>
                        <h3 id="stat-res-today">-</h3>
                        <small id="stat-res-total">Upcoming: -</small>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fff3e0; color: #ef6c00;"><i class="fas fa-chair"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Active Tables</span>
                        <h3 id="stat-active-tables">-</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e3f2fd; color: #1565c0;"><i class="fas fa-coffee"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Menu Items</span>
                        <h3 id="stat-menu-items">-</h3>
                    </div>
                </div>
                <div class="stat-card">
                     <!-- Quick Action for Walk-in -->
                     <button class="btn btn-secondary" onclick="openQuickReserve()" style="width:100%; height:100%; display:flex; flex-direction:column; justify-content:center; align-items:center;">
                        <i class="fas fa-user-plus" style="font-size: 1.5rem; margin-bottom: 5px;"></i>
                        Walk-In Reservation
                     </button>
                </div>
            </div>

            <div class="dashboard-split" style="display: flex; gap: 20px; flex-wrap: wrap;">
                <!-- Main Chart -->
                <div class="chart-container" style="flex: 2; min-width: 300px; background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #eee;">
                    <h4>Orders Overview (Last 7 Days)</h4>
                    <canvas id="reservationsChart"></canvas>
                </div>

                <!-- Notes Board -->
                <div class="notes-container" style="flex: 1; min-width: 250px; background: #fffaf0; padding: 20px; border-radius: 12px; border: 1px solid #fae5ba;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <h4 style="margin:0; color: #795548;"><i class="fas fa-sticky-note"></i> Staff Notes</h4>
                        <small id="notes-status" style="color: green; display:none;">Saved</small>
                    </div>
                    <textarea id="admin-notes" style="width: 100%; height: 200px; border: none; background: transparent; resize: none; font-family: 'Courier New', monospace;" placeholder="Type notes here..."></textarea>
                </div>
            </div>

            <!-- Recent Activity & Quick Tools -->
            <div class="recent-section" style="margin-top: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3>Recent Reservations</h3>
                    <div>
                        <button class="btn btn-sm btn-primary" onclick="exportData('reservations')"><i class="fas fa-file-csv"></i> Export Reservations</button>
                        <button class="btn btn-sm btn-secondary" onclick="exportData('users')"><i class="fas fa-file-csv"></i> Export Users</button>
                    </div>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Name</th>
                            <th>Create Time</th>
                            <th>Reservation Date</th>
                            <th>ID</th>
                        </tr>
                    </thead>
                    <tbody id="recent-activity-list">
                        <!-- Populated JS -->
                    </tbody>
                </table>
            </div>
            
            <!-- Newsletter Widget -->
             <div class="newsletter-widget" style="margin-top: 30px; background: #f0f4c3; padding: 20px; border-radius: 12px;">
                <details>
                    <summary style="font-weight: bold; cursor: pointer; color: #827717;">Send Newsletter / Announcement</summary>
                    <div style="margin-top: 15px;">
                        <input type="text" id="news-subject" class="form-control" placeholder="Subject" style="margin-bottom: 10px;">
                        <textarea id="news-body" class="form-control" placeholder="Message to all users..." style="margin-bottom: 10px;"></textarea>
                        <button class="btn btn-success" onclick="sendNewsletter()">Send Email</button>
                    </div>
                </details>
             </div>
        </section>

        <!-- Running Orders Section -->
        <section id="section-orders" class="admin-section" style="display: none;">
            <div class="header-actions">
                <h2>Running Orders</h2>
            </div>
            <div class="placeholder-content">
                <p>Order management interface coming soon.</p>
                <!-- Reuse logic from previous if needed, but for now placeholder is fine or implement if requested separately -->
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
                <h2>Floor Plan & Tables</h2>
                <div class="table-controls" style="display: flex; align-items: center; gap: 15px;">
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
                <p>Detailed List:</p>
            </div>
        </section>

        <!-- Reservations Section -->
        <section id="section-reservations" class="admin-section" style="display: none;">
            <div class="header-actions">
                <h2>Reservations</h2>
            </div>
            <div class="table-container">
                <h3 class="section-subtitle">Active & Upcoming</h3>
                <table class="data-table" id="reservations-table">
                    <thead>
                        <tr>
                            <th>DateTime</th>
                            <th>Table</th>
                            <th>Name</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reservations-list">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>

            <div class="history-section" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <details>
                    <summary style="font-size: 1.2rem; font-weight: bold; cursor: pointer; color: #7f8c8d;">
                        View Reservation History (Past) <i class="fas fa-chevron-down" style="font-size: 0.8rem; margin-left: 5px;"></i>
                    </summary>
                    <div class="table-container" style="margin-top: 15px;">
                        <table class="data-table" id="history-reservations-table" style="opacity: 0.8;">
                            <thead>
                                <tr>
                                    <th>DateTime</th>
                                    <th>Table</th>
                                    <th>Name</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="history-reservations-list">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>

            <div class="deleted-section" style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 20px;">
                <details>
                    <summary style="font-size: 1.2rem; font-weight: bold; cursor: pointer; color: #c0392b;">
                        View Deleted Reservations <i class="fas fa-trash" style="font-size: 0.8rem; margin-left: 5px;"></i>
                    </summary>
                    <div class="table-container" style="margin-top: 15px;">
                        <table class="data-table" id="deleted-reservations-table" style="background: #fff0f0;">
                            <thead>
                                <tr>
                                    <th>DateTime</th>
                                    <th>Table</th>
                                    <th>Name</th>
                                    <th>User</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="deleted-reservations-list">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
        </section>

        <!-- Users Section (NEW) -->
        <section id="section-users" class="admin-section" style="display: none;">
             <div class="header-actions">
                <h2>User Management</h2>
                <input type="text" id="user-search" placeholder="Search by name or email..." class="form-control" style="width: 300px;">
            </div>
            <div class="table-container">
                <table class="data-table" id="users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Settings Section (NEW) -->
        <section id="section-settings" class="admin-section" style="display: none;">
            <div class="header-actions">
                <h2>Business Settings</h2>
            </div>
            
            <div class="settings-card" style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #eee;">
                <h3>Working Hours Schedule</h3>
                <p>Configure when the cafe is open. Reservations and Orders will only be allowed during these times.</p>
                <form id="schedule-form">
                    <table class="data-table" style="width: 100%; max-width: 600px;">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Open Time</th>
                                <th>Close Time</th>
                                <th>Closed?</th>
                            </tr>
                        </thead>
                        <tbody id="schedule-list">
                            <!-- Populated JS -->
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-success" style="margin-top: 20px;">Save Schedule</button>
                </form>
            </div>
        </section>

    </main>
</div>

<!-- Add/Edit Product Modal -->
<div id="product-modal" class="modal">
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

<!-- User Details Modal -->
<div id="user-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal" data-target="user-modal">&times;</span>
        <h3>User Details</h3>
        <div id="user-details-content">
            <!-- Populated via JS -->
             <p>Loading...</p>
        </div>
        <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
             <h4>Actions</h4>
             <button id="blacklist-btn" class="btn btn-danger" style="width: 100%;">Toggle Blacklist</button>
             <textarea id="blacklist-reason" placeholder="Reason for blacklisting (required)" style="width:100%; margin-top: 10px; display:none;"></textarea>
        </div>
    </div>
</div>

<!-- Quick Reservation Modal -->
<div id="quick-reserve-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal" data-target="quick-reserve-modal">&times;</span>
        <h3>Walk-In / Quick Reservation</h3>
        <form id="quick-reserve-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="entity" value="reservation">
            <input type="hidden" name="is_admin_walkin" value="1">
            
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required placeholder="Guest Name">
            </div>
            
             <div class="form-group">
                 <label>Table</label>
                 <!-- Ideally populated dynamically, but simple input for now or select -->
                 <input type="number" name="table_id" required placeholder="Table ID" min="1">
             </div>

            <div class="form-group">
                <label>Date & Time</label>
                <input type="datetime-local" name="reservation_time" required>
            </div>
            
            <div class="form-group">
                <label>Email (Optional)</label>
                <input type="email" name="email" placeholder="For confirmation">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Create Reservation</button>
        </form>
    </div>
</div>

<script src="assets/js/admin.js?v=<?= time(); ?>"></script>
