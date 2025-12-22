// Mazi Coffee Admin Dashboard Script

// Helper to safely assign globals
window.openEdit = openEdit;
window.deleteProduct = deleteProduct;
window.editProduct = openEdit; // Alias if needed

// Immediate initialization when script is loaded
(function initAdmin() {
    console.log("Admin Dashboard Script Initializing...");

    // Load data immediately
    loadProducts();

    // Setup Modal & Form Listeners
    const modal = document.getElementById('product-modal');
    const closeBtn = document.querySelector('.close-modal');
    const addBtn = document.getElementById('add-product-btn');
    const form = document.getElementById('product-form');

    if (addBtn) {
        addBtn.onclick = function () {
            resetForm();
            document.getElementById('modal-title').innerText = 'Add New Coffee';
            document.getElementById('form-action').value = 'add';
            document.getElementById('current-image-preview').style.display = 'none';
            if (modal) modal.style.display = "block";
        };
    }

    if (closeBtn) {
        closeBtn.onclick = function () {
            if (modal) modal.style.display = "none";
        };
    }

    window.onclick = function (event) {
        if (modal && event.target == modal) {
            modal.style.display = "none";
        }
    };

    if (form) {
        form.onsubmit = function (e) {
            e.preventDefault();
            console.log("Submitting Product Form...");

            const formData = new FormData(form);
            const action = document.getElementById('form-action').value;
            formData.set('action', action);

            fetch('controllers/admin_handler.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // alert(data.data.message || 'Success');
                        if (modal) modal.style.display = "none";
                        loadProducts();
                    } else {
                        alert('Error: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Form Submit Error:', error);
                    alert('Connection error');
                });
        };
    }
})();

function loadProducts() {
    console.log("Fetching products...");
    const tbody = document.querySelector('#products-table tbody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Loading...</td></tr>';

    const formData = new FormData();
    formData.append('action', 'get_all');

    fetch('controllers/admin_handler.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
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

// Global store for current products to avoid re-fetching for edit
let currentProducts = [];

function renderTable(products) {
    currentProducts = products;
    const tbody = document.querySelector('#products-table tbody');
    if (!tbody) return;

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

    resetForm();
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
        // Fix image cache if needed
    } else {
        preview.style.display = 'none';
    }

    const modal = document.getElementById('product-modal');
    if (modal) modal.style.display = "block";
}

function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product?')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('controllers/admin_handler.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadProducts();
            } else {
                alert('Error: ' + data.error);
            }
        });
}

function resetForm() {
    const form = document.getElementById('product-form');
    if (form) form.reset();
    document.getElementById('prod-id').value = '';
    const preview = document.getElementById('current-image-preview');
    if (preview) preview.style.display = 'none';
}

function safe(str) {
    if (!str) return '';
    return str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
