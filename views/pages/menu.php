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
<style>
    .add-to-cart-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: inherit;
        font-size: 1.2rem;
        padding: 5px;
        transition: transform 0.2s, color 0.2s;
    }
    .add-to-cart-btn:hover {
        transform: scale(1.2);
        color: #D2691E;
    }
</style>

<section class="menu-section">
    <div class="menu-container">
        <h2 class="section-title">Selectia noastră</h2>

        <div class="menu-search">
            <div class="search-input-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    id="menuSearchInput"
                    placeholder="Caută aici" />
                <span class="search-pill">Caută</span>
            </div>
        </div>

        <div class="no-results" style="display: none;">
            <span class="not-found-pill">Ne pare rău, nu am gasit niciun produs!</span>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="menu-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="product-card animate-on-scroll">
                        <div class="product-image">
                            <!-- Use relative path from root is assumed, or ensure path from DB is correct -->
                            <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" loading="lazy">
                        </div>
                        <div class="product-info">
                            <div class="product-header">
                                <h3 class="product-name">
                                    <?= htmlspecialchars($row['name']) ?>
                                </h3>
                                <div class="product-price">
                                    <button class="add-to-cart-btn" data-id="<?= $row['id'] ?>" title="Add to Cart">
                                        <i class="fa-solid fa-cart-shopping"></i>
                                    </button>
                                    <?= number_format($row['price'], 2) ?> RON
                                </div>
                            </div>
                            <p class="product-description">
                                <?= htmlspecialchars($row['description']) ?>
                            </p>
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

<script>
    const searchInput = document.getElementById('menuSearchInput');
    const products = document.querySelectorAll('.product-card');
    const noResults = document.querySelector('.no-results');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        let anyVisible = false;

        products.forEach(product => {
            const name = product.querySelector('.product-name').textContent.toLowerCase();
            if (name.includes(query)) {
                product.style.display = 'block';
                anyVisible = true;
            } else {
                product.style.display = 'none';
            }
        });

        if (noResults) {
            noResults.style.display = anyVisible ? 'none' : 'flex';
        }
    });
</script>