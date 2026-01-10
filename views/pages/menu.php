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

// --- FILTERING LOGIC ---
$where = [];
$params = [];
$types = "";

// 1. Category Filter
$category = $_GET['cat'] ?? 'all';
if ($category !== 'all') {
    $where[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}

// 2. Search Filter (Server Side)
$searchQuery = $_GET['q'] ?? '';
if (!empty($searchQuery)) {
    $where[] = "(name LIKE ? OR description LIKE ?)";
    $like = "%" . $searchQuery . "%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

// Construct SQL
$sql = "SELECT * FROM products";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// 3. Sorting
$sort = $_GET['sort'] ?? 'name_asc';
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY name DESC";
        break;
    default:
        $sql .= " ORDER BY name ASC";
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

<link rel="stylesheet" href="assets/css/menu.css?v=<?= time() ?>">

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

                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="menu-grid" id="menu-grid">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="product-card animate-on-scroll" 
                                 data-name="<?= htmlspecialchars($row['name']) ?>"
                                 data-desc="<?= htmlspecialchars($row['description']) ?>"
                                 data-price="<?= number_format($row['price'], 2) ?>"
                                 data-img="<?= htmlspecialchars($row['image_path']) ?>"
                                 data-ingredients="<?= htmlspecialchars($row['ingredients'] ?? '') ?>">
                                <div class="product-image">
                                    <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" loading="lazy" onerror="this.src='assets/menu/images/default_coffee.jpg'">
                                </div>
                                <div class="product-info">
                                    <div class="product-header">
                                        <h3 class="product-name"><?= htmlspecialchars($row['name']) ?></h3>
                                        <div class="product-price">
                                            <button class="add-to-cart-btn-full" data-id="<?= $row['id'] ?>">
                                                <i class="fa-solid fa-cart-shopping"></i>
                                                <span><?= number_format($row['price'], 2) ?> RON</span>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="product-description"><?= htmlspecialchars($row['description']) ?></p>
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
                const newContent = doc.querySelector('.menu-section').innerHTML;
                
                container.innerHTML = newContent;
                
                // Re-initialize scripts (since we replaced the script tag too potentially, or at least the logic)
                // Actually, replacing innerHTML of container kills this script execution context if it was inside?
                // No, this script is OUTSIDE .menu-section in my layout structure? 
                // Wait, in the file I put <script> at the bottom.
                // If I replace .menu-section innerHTML, the <script> is outside, so it persists.
                // BUT the event listeners inside .menu-section are gone.
                // We need to re-attach listeners.
                attachListeners();
                
                // Update Browser URL (optional, but good for shareability)
                // We should prepend ?page=menu
                const newUrl = `?page=menu&${qs}`;
                window.history.pushState({page: 'menu'}, '', newUrl);

            } catch(e) {
                console.error(e);
                if(grid) grid.style.opacity = '1';
                alert("Failed to load products.");
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

            // 3. Search
            const searchInput = document.getElementById('menuSearchInput');
            const searchBtn = document.getElementById('search-btn');
            
            const handleSearch = () => {
                const val = searchInput.value.trim();
                const currentParams = new URLSearchParams(window.location.search);
                currentParams.delete('page');
                if(val) currentParams.set('q', val);
                else currentParams.delete('q');
                
                fetchFilteredContent(currentParams);
            };

            if(searchBtn) searchBtn.addEventListener('click', handleSearch);
            if(searchInput) {
                searchInput.addEventListener('keypress', (e) => {
                    if(e.key === 'Enter') handleSearch();
                });
            }
        }

        // Init
        attachListeners();
    })();
</script>