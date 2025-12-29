// Mazi Coffee Admin Dashboard Script

(function () {
    // Helper to safely assign globals
    window.openEdit = openEdit;
    window.deleteProduct = deleteProduct;
    window.deleteSlide = deleteSlide;

    // Global store
    let currentProducts = [];

    // Immediate initialization
    console.log("Admin Dashboard Script Initializing...");

    // --- Navigation Logic ---
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    const sections = document.querySelectorAll('.admin-section');

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-section');
            if (!targetId) return;

            // Update Nav Active State
            navLinks.forEach(n => n.classList.remove('active'));
            this.classList.add('active');

            // Show Target Section
            sections.forEach(s => s.style.display = 'none');
            const targetSection = document.getElementById('section-' + targetId);
            if (targetSection) {
                targetSection.style.display = 'block';
                // Lazy load data based on section
                if (targetId === 'menu') {
                    if (document.querySelectorAll('#products-table tbody tr').length === 0) {
                        loadProducts();
                    }
                } else if (targetId === 'slider') {
                    loadSliderImages();
                }
            }
        });
    });

    // --- Generic Modal Logic ---
    const modals = document.querySelectorAll('.modal');
    const closeBtns = document.querySelectorAll('.close-modal');

    // Close buttons
    closeBtns.forEach(btn => {
        btn.onclick = function () {
            const targetId = this.getAttribute('data-target');
            if (targetId) {
                document.getElementById(targetId).style.display = 'none';
            } else {
                this.closest('.modal').style.display = 'none';
            }
        }
    });

    // Click outside to close
    window.onclick = function (event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };

    // --- Product Logic ---
    const productModal = document.getElementById('product-modal');
    const addProductBtn = document.getElementById('add-product-btn');
    const productForm = document.getElementById('product-form');

    if (addProductBtn) {
        addProductBtn.onclick = function () {
            resetProductForm();
            document.getElementById('modal-title').innerText = 'Add New Coffee';
            document.getElementById('form-action').value = 'add';
            if (productModal) productModal.style.display = "block";
        };
    }

    if (productForm) {
        productForm.onsubmit = function (e) {
            e.preventDefault();
            handleFormSubmit(productForm, productModal, loadProducts);
        };
    }

    function loadProducts() {
        console.log("Fetching products...");
        const tbody = document.querySelector('#products-table tbody');
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Loading...</td></tr>';

        const formData = new FormData();
        formData.append('action', 'get_all');
        appendCsrf(formData);

        fetch('controllers/admin_handler.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderTable(data.data);
                } else {
                    console.error("API Error:", data.error);
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Error loading products.</td></tr>';
                }
            })
            .catch(err => {
                console.error("Fetch Error:", err);
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Connection error.</td></tr>';
            });
    }

    function renderTable(products) {
        currentProducts = products;
        const tbody = document.querySelector('#products-table tbody');
        if (!tbody) return; // Should not happen

        tbody.innerHTML = '';
        if (!products || products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No products found.</td></tr>';
            return;
        }

        products.forEach(p => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><img src="${p.image_path}" alt="${safe(p.name)}" onerror="this.src='assets/public/default.png'"></td>
                <td><strong>${safe(p.name)}</strong></td>
                <td><span class="badge">${safe(p.category)}</span></td>
                <td>${parseFloat(p.price).toFixed(2)} RON</td>
                <td>
                    <button class="btn btn-edit" onclick="openEdit(${p.id})"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn btn-danger" onclick="deleteProduct(${p.id})"><i class="fas fa-trash"></i> Delete</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function openEdit(id) {
        const p = currentProducts.find(x => x.id == id);
        if (!p) return;

        resetProductForm();
        document.getElementById('modal-title').innerText = 'Edit Coffee';
        document.getElementById('form-action').value = 'update';
        document.getElementById('prod-id').value = p.id;
        document.getElementById('prod-name').value = p.name;
        document.getElementById('prod-price').value = p.price;
        document.getElementById('prod-desc').value = p.description || '';
        document.getElementById('prod-category').value = p.category || 'coffee';

        const preview = document.getElementById('current-image-preview');
        const img = document.getElementById('preview-img');

        if (p.image_path) {
            preview.style.display = 'block';
            img.src = p.image_path;
        } else {
            preview.style.display = 'none';
        }

        if (productModal) productModal.style.display = "block";
    }

    function deleteProduct(id) {
        if (!confirm('Are you sure you want to delete this product?')) return;
        postAction({ action: 'delete', id: id }, loadProducts);
    }

    function resetProductForm() {
        if (productForm) productForm.reset();
        document.getElementById('prod-id').value = '';
        const preview = document.getElementById('current-image-preview');
        if (preview) preview.style.display = 'none';
    }

    // --- Slider Logic ---
    const sliderModal = document.getElementById('slider-modal');
    const addSlideBtn = document.getElementById('add-slide-btn');
    const sliderForm = document.getElementById('slider-form');
    // We already have generic close logic for slider-modal

    if (addSlideBtn) {
        addSlideBtn.onclick = function () {
            if (sliderForm) sliderForm.reset();
            if (sliderModal) sliderModal.style.display = 'block';
        }
    }

    if (sliderForm) {
        sliderForm.onsubmit = function (e) {
            e.preventDefault();
            handleFormSubmit(sliderForm, sliderModal, loadSliderImages);
        }
    }

    function loadSliderImages() {
        console.log("Fetching slides...");
        const container = document.getElementById('slider-list');
        if (!container) return;

        container.innerHTML = '<p>Loading...</p>';

        const formData = new FormData();
        formData.append('action', 'get_all');
        formData.append('entity', 'slider');
        appendCsrf(formData);

        fetch('controllers/admin_handler.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    renderSliderList(data.data);
                } else {
                    container.innerHTML = '<p>Error loading slides.</p>';
                }
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<p>Connection Error.</p>';
            });
    }

    function renderSliderList(slides) {
        const container = document.getElementById('slider-list');
        if (!container) return;
        container.innerHTML = '';

        if (!slides.length) {
            container.innerHTML = '<p>No slides found.</p>';
            return;
        }

        const grid = document.createElement('div');
        grid.className = 'slider-grid';

        slides.forEach(s => {
            const card = document.createElement('div');
            card.className = 'slide-card';
            card.innerHTML = `
                <img src="${s.image_path}" alt="Slide">
                <div class="slide-info">
                    <strong>${safe(s.title || 'No Title')}</strong>
                    <p>${safe(s.subtitle || '')}</p>
                    <button class="btn btn-danger btn-sm" onclick="deleteSlide(${s.id})">Delete</button>
                </div>
            `;
            grid.appendChild(card);
        });
        container.appendChild(grid);
    }

    function deleteSlide(id) {
        if (!confirm('Delete this slide?')) return;
        postAction({ action: 'delete', entity: 'slider', id: id }, loadSliderImages);
    }

    // --- Shared Utilities ---

    function appendCsrf(formData) {
        const csrfCtx = document.getElementById('csrf-token-global');
        if (csrfCtx) formData.append('csrf_token', csrfCtx.value);
    }

    function handleFormSubmit(form, modalToClose, reloadCallback) {
        const formData = new FormData(form);

        fetch('controllers/admin_handler.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (modalToClose) modalToClose.style.display = 'none';
                    if (reloadCallback) reloadCallback();
                } else {
                    alert('Error: ' + (data.error || 'Unknown'));
                }
            })
            .catch(err => {
                console.error("Submit Error:", err);
                alert('Connection Error');
            });
    }

    function postAction(dataObj, reloadCallback) {
        const formData = new FormData();
        for (const k in dataObj) {
            formData.append(k, dataObj[k]);
        }
        appendCsrf(formData);

        fetch('controllers/admin_handler.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (reloadCallback) reloadCallback();
                } else {
                    alert('Error: ' + data.error);
                }
            });
    }

    function safe(str) {
        if (!str) return '';
        return str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Initial Load based on default visible section
    // Check which section is active or default to menu
    if (document.getElementById('section-menu').style.display !== 'none') {
        loadProducts();
    } else if (document.getElementById('section-slider').style.display !== 'none') {
        loadSliderImages();
    }

})();
