<?php
// SAFETY GATE: Block direct access to this file
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    exit('Direct access denied.');
}
// Verify database connection is available
if (!isset($conn)) {
    require_once __DIR__ . '/../../config/db.php';
}
?>

<?php

// --- FILTERING LOGIC ---
$where = [];
$params = [];
$types = "";

// 1. Category Filter
$category = $_GET['cat'] ?? 'all';
if ($category !== 'all') {
    $where[] = "p.category = ?";
    $params[] = $category;
    $types .= "s";
}

// 2. Search Filter (Server Side)
$searchQuery = $_GET['q'] ?? '';
$searchQuery = $_GET['q'] ?? '';
if (!empty($searchQuery)) {
    // Search in Name OR Tags (Excluded Description as per request)
    $where[] = "(p.name LIKE ? OR p.tags LIKE ?)";
    $like = "%" . $searchQuery . "%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

// Construct SQL
$sql = "SELECT p.*, GROUP_CONCAT(i.name SEPARATOR ', ') as linked_ingredients_names 
        FROM products p
        LEFT JOIN product_ingredients pi ON p.id = pi.product_id
        LEFT JOIN ingredients i ON pi.ingredient_id = i.id";

if (!empty($where)) {
    // Note: We need to specify table alias for potentially ambiguous columns
    $where = array_map(function($cond) {
        // Simple heuristic: if query touches 'category', 'name', 'tags', prefix with 'p.'
        // Actually, $where was built above. Let's fix the build logic above?
        // Or cleaner: Rebuild $where logic or just prefix here if simple.
        // It's safer to prefix usage above.
        // Let's assume standard columns like 'category' need 'p.category'.
        return $cond; // For now, let's leave as is and hope no ambiguity with ingredients table columns?
        // 'category', 'name', 'tags', 'price' are in products. 'name' is in ingredients too! AMBIGUITY!
        // I MUST FIX THE WHERE CLAUSE GENERATION.
    }, $where);
}
// Wait, I cannot change the WHERE generation inside this chunk easily if it matches broadly. 
// I should replace the whole block including WHERE generation.

// RE-WRITING THE WHOLE LOGIC BLOCK.
$sql = "SELECT p.*, GROUP_CONCAT(i.name SEPARATOR ', ') as linked_ingredients_names 
        FROM products p
        LEFT JOIN product_ingredients pi ON p.id = pi.product_id
        LEFT JOIN ingredients i ON pi.ingredient_id = i.id";
        
if (!empty($where)) {
    // PREPEND p. to ambiguous columns in the where clause
    // 'category' -> 'p.category'
    // 'name' -> 'p.name'
    // 'tags' -> 'p.tags'
    // 'price' -> 'p.price' (if used in sort, but sort is handled below)
    
    // Actually, I should just fix the $where construction lines earlier.
    // But I limited my Replacement to lines 41-62? No, I can expand.
    // Let's replace the whole top block to be safe.
    
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Group By is needed for aggregation
$sql .= " GROUP BY p.id";

// 3. Sorting
$sort = $_GET['sort'] ?? 'name_asc';
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY p.name DESC";
        break;
    default:
        $sql .= " ORDER BY p.name ASC";
        break;
}

// Execute Query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch Categories for Sidebar logic
$catSql = "SELECT DISTINCT category FROM products ORDER BY category ASC";
$catResult = $conn->query($catSql);
$categories = [];
while ($c = $catResult->fetch_assoc()) {
    $categories[] = $c['category'];
}
?>


<section class="menu-section">
    <div class="menu-container">
        <h2 class="section-title">Selectia noastră</h2>

        <div class="menu-layout">
            <!-- Sidebar Filters -->
            <aside class="menu-sidebar animate-on-scroll">
                <div class="filter-group">
                    <h3><i class="fa-solid fa-mug-hot"></i> Products</h3>
                    <ul class="filter-list">
                        <li>
                            <a href="#" class="filter-link <?= $category === 'all' ? 'active' : '' ?>" data-filter-type="cat" data-filter-val="all">
                                All Products
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="#" class="filter-link <?= $category === $cat ? 'active' : '' ?>" data-filter-type="cat" data-filter-val="<?= htmlspecialchars($cat) ?>">
                                    <?= ucfirst(htmlspecialchars($cat)) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="filter-group">
                    <h3><i class="fa-solid fa-sort"></i> Sort By</h3>
                    <select id="sort-select" class="form-control">
                        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                        <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
                    </select>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="menu-content">
                <!-- Search Bar -->
                <div class="menu-search">
                    <div class="search-input-wrapper">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="menuSearchInput" placeholder="Caută produse..." value="<?= htmlspecialchars($searchQuery) ?>">
                        <button id="search-btn" class="search-pill" style="border:none; cursor:pointer;">Caută</button>
                    </div>
                </div>

                <div id="results-container">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="menu-grid" id="menu-grid">
                            <?php while ($row = $result->fetch_assoc()): 
                                $price = floatval($row['price']);
                                $discount = intval($row['discount'] ?? 0);
                                $finalPrice = $price;
                                $oldPriceHtml = '';
                                $discountBadge = '';
    
                                if ($discount > 0) {
                                    $finalPrice = $price - ($price * $discount / 100);
                                    $oldPriceHtml = '<span style="text-decoration: line-through; color: #999; font-size: 0.9em; margin-right: 5px;">' . number_format($price, 2) . '</span>';
                                    $discountBadge = '<div class="discount-pill" >-' . $discount . '%</div>';
                                }
                            ?>
                                <div class="product-card animate-on-scroll" 
                                     style="position: relative;"
                                     data-name="<?= htmlspecialchars($row['name']) ?>"
                                     data-desc="<?= htmlspecialchars($row['description']) ?>"
                                     data-price="<?= number_format($price, 2) ?>"
                                     data-discount="<?= $discount ?>"
                                     data-img="<?= htmlspecialchars($row['image_path']) ?>"
                                     data-ingredients="<?= htmlspecialchars($row['linked_ingredients_names'] ?? '') ?>"
                                     data-quantity="<?= intval($row['quantity']) ?>">
                                    
                                    <?= $discountBadge ?>
    
                                    <div class="product-image">
                                        <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" loading="lazy" onerror="this.src='assets/menu/images/default_coffee.jpg'">
                                    </div>
                                    <div class="product-info">
                                        <div class="product-header">
                                            <h3 class="product-name"><?= htmlspecialchars($row['name']) ?></h3>
                                            <div class="product-price">
                                                <button class="add-to-cart-btn-full" data-id="<?= $row['id'] ?>">
                                                    <i class="fa-solid fa-cart-shopping"></i>
                                                    <span><?= $oldPriceHtml . number_format($finalPrice, 2) ?> RON</span>
                                                </button>
                                            </div>
                                        </div>
                                        <p class="product-description"><?= htmlspecialchars($row['description']) ?></p>
                                    
                                    <?php if (!empty($row['linked_ingredients_names'])): ?>
                                        <p class="product-ingredients" style="color: #555; font-size: 0.85rem; margin-top: 5px; font-style: italic;">
                                            <i class="fa-solid fa-leaf" style="color:#2c6e49; margin-right:3px;"></i>
                                            <?= htmlspecialchars($row['linked_ingredients_names']) ?>
                                        </p>
                                    <?php endif; ?>

                                    <span class="product-quantity"><?= intval ($row['quantity']) ?> ml</span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-results">
                            <span class="not-found-pill">Ne pare rău, nu am găsit niciun produs!</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Details Modal -->
<div id="product-details-modal" class="modal">
    <div class="modal-content product-modal-content">
        <span class="close-modal">&times;</span>
        <div class="modal-body-flex">
            <div class="modal-img-wrapper">
                <img id="modal-prod-img" src="" alt="Product">
            </div>
            <div class="modal-info-wrapper">
                <h3 id="modal-prod-name"></h3>
                <p id="modal-prod-desc" class="modal-desc"></p>
                <div class="ingredients-section" id="modal-ingredients-section">
                    <h4>Ingrediente:</h4>
                    <p id="modal-prod-ingredients"></p>
                </div>
                <div class="modal-price-action">
                    <span id="modal-prod-price" class="modal-price"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const container = document.querySelector('.menu-section');

        // Helper: Fetch and replace content
        async function fetchFilteredContent(params) {
            // Show loading state
            const grid = document.getElementById('menu-grid');
            if(grid) grid.style.opacity = '0.5';

            try {
                // Construct URL for internal fetch
                // We use views/pages/menu.php directly but need to ensure security checks pass
                // OR we can rely on main Router to NOT do full reload? 
                // Using view_path directly:
                const qs = new URLSearchParams(params).toString();
                const url = `views/pages/menu.php?${qs}`;
                
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                if(!res.ok) throw new Error("Load failed");
                
                const html = await res.text();
                
                // Parse HTML to extract the new container content
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('results-container').innerHTML;
                
                // Target the specific results container NOT the whole section
                const resultsContainer = document.getElementById('results-container');
                if(resultsContainer) {
                    resultsContainer.innerHTML = newContent;
                    
                    // Trigger reflow to restart animations if any (standard practice)
                    // Apply staggered animation
                    const cards = resultsContainer.querySelectorAll('.product-card');
                    cards.forEach((card, index) => {
                        card.classList.add('stagger-item');
                        card.style.animationDelay = `${index * 0.05}s`; // 50ms delay per card
                    });
                }
                
                // No need to re-attach listeners if they are delegated or outside.
                // But wait, the listeners we attached were:
                // 1. Sidebar Links (OUTSIDE results-container) -> Safe
                // 2. Sort Dropdown (OUTSIDE) -> Safe
                // 3. Search Input (OUTSIDE) -> Safe
                // So actually, we DON'T need to call attachListeners() again!
                
                // Update Browser URL
                const newUrl = `?page=menu&${qs}`;
                window.history.pushState({page: 'menu'}, '', newUrl);

            } catch(e) {
                console.error(e);
                if(grid) grid.style.opacity = '1';
                // alert("Failed to load products."); // Silent fail on typeahead is better
            }
        }

        function attachListeners() {
            // 1. Sidebar Links
            document.querySelectorAll('.filter-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const type = link.dataset.filterType;
                    const val = link.dataset.filterVal;
                    
                    const currentParams = new URLSearchParams(window.location.search);
                    currentParams.delete('page'); // Clean up for logic
                    
                    if (type === 'cat') {
                        currentParams.set('cat', val);
                        // Reset search if changing category? Optional. Let's keep filters additive if possible, but usually separate.
                    }
                    
                    fetchFilteredContent(currentParams);
                });
            });

            // 2. Sort Dropdown
            const sortSelect = document.getElementById('sort-select');
            if(sortSelect) {
                sortSelect.addEventListener('change', (e) => {
                    const val = e.target.value;
                    const currentParams = new URLSearchParams(window.location.search);
                    currentParams.delete('page');
                    currentParams.set('sort', val);
                    fetchFilteredContent(currentParams);
                });
            }

            // 3. Search (Instant with Debounce)
            const searchInput = document.getElementById('menuSearchInput');
            const searchBtn = document.getElementById('search-btn');
            let searchTimeout;

            const performSearch = (val) => {
                const currentParams = new URLSearchParams(window.location.search);
                currentParams.delete('page');
                if(val) currentParams.set('q', val);
                else currentParams.delete('q');
                
                fetchFilteredContent(currentParams);
            };

            const handleSearch = () => {
                const val = searchInput.value.trim();
                performSearch(val);
            };

            if(searchBtn) {
                searchBtn.addEventListener('click', handleSearch);
            }

            if(searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const val = e.target.value.trim();
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        performSearch(val);
                    }, 300); // 300ms debounce
                });

                // Keep Enter for immediate search
                searchInput.addEventListener('keypress', (e) => {
                    if(e.key === 'Enter') {
                        clearTimeout(searchTimeout);
                        handleSearch();
                    }
                });
            }
        }

        // Init
        attachListeners();
    })();
</script>