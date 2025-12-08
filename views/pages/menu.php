<?php
// SAFETY GATE: Block direct access to this file
// This ensures the file can only be loaded via the implementation's safeFetch (AJAX)
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
// Verify database connection is available
if (!isset($conn)) {
    require_once __DIR__ . '/../../config/db.php';
}

// Fetch products
$sql = "SELECT * FROM products WHERE category = 'coffee' ORDER BY name ASC";
$result = $conn->query($sql);
?>
<link rel="stylesheet" href="assets/css/menu.css">

<section class="menu-section">
    <div class="menu-container">
        <h2 class="section-title">Our Coffee Selection</h2>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="menu-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <!-- Use relative path from root is assumed, or ensure path from DB is correct -->
                            <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" loading="lazy">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($row['name']) ?></h3>
                            <p class="product-description"><?= htmlspecialchars($row['description']) ?></p>
                            <div class="product-price"><?= number_format($row['price'], 2) ?> RON</div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <p>We are currently updating our menu. Please check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</section>