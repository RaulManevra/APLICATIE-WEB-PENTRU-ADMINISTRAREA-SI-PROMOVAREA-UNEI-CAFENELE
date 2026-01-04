// Mazi Coffee Admin Dashboard Script

(function () {
    // Helper to safely assign globals
    window.openEdit = openEdit;
    window.deleteProduct = deleteProduct;
    window.deleteSlide = deleteSlide;
    // blocked user func moved to window directly

    // Global store
    let currentProducts = [];

    // Immediate initialization
    console.log("Admin Dashboard Script Initializing...");

    // --- Navigation Logic ---
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    const sections = document.querySelectorAll('.admin-section');
    let reservationInterval = null; // Track auto-refresh

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-section');
            if (!targetId) return;

            // Clear any active interval when switching sections
            if (reservationInterval) {
                clearInterval(reservationInterval);
                reservationInterval = null;
            }

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
                } else if (targetId === 'tables') {
                    loadTables();
                } else if (targetId === 'reservations') {
                    loadReservations();
                    // Start Auto-Refresh (5 minutes = 300,000 ms)
                    reservationInterval = setInterval(loadReservations, 300000);
                    console.log("Auto-refresh enabled for reservations (every 5m).");
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
                // Pass full data to callback, let it handle success check if needed
                // OR maintain existing behavior: only call if success? 
                // But loadReservations NEEDS data even if success check is inside.
                // Best compatible change:
                if (data.success) {
                    if (reloadCallback) reloadCallback(data);
                } else {
                    // Try to handle custom error callbacks or just alert
                    // If callback handles it?
                    if (reloadCallback && reloadCallback.length > 0) {
                        // If callback expects argument, maybe it wants to handle error too? 
                        // But strictly: postAction implies "Do X".
                        // Let's call callback with data anyway if we are fetching?
                        // No, mixed semantics. 
                        // Let's just pass data.
                        if (reloadCallback) reloadCallback(data);
                        return;
                    }
                    alert('Error: ' + (data.message || data.error || 'Unknown error'));
                }
            })
            .catch(e => {
                console.error(e);
                if (reloadCallback) reloadCallback({ success: false, error: e.toString() });
                else alert('Network Error');
            });
    }

    function safe(str) {
        if (!str) return '';
        return str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // --- Table Logic ---
    const addTableBtn = document.getElementById('add-table-btn');
    const removeTableBtn = document.getElementById('remove-table-btn');

    if (addTableBtn) {
        addTableBtn.onclick = function () {
            const current = document.querySelectorAll('.table-card').length;
            postAction({ action: 'update_count', entity: 'table', count: current + 1 }, loadTables);
        }
    }

    if (removeTableBtn) {
        removeTableBtn.onclick = function () {
            const current = document.querySelectorAll('.table-card').length;
            if (current <= 0) return;
            if (!confirm('Remove the last table?')) return;
            postAction({ action: 'update_count', entity: 'table', count: current - 1 }, loadTables);
        }
    }

    let currentTables = [];

    // Background Upload Logic
    const uploadFloorPlanBtn = document.getElementById('upload-floor-plan-btn');
    const floorPlanInput = document.getElementById('floor-plan-upload');

    if (uploadFloorPlanBtn && floorPlanInput) {
        uploadFloorPlanBtn.onclick = () => floorPlanInput.click();

        floorPlanInput.onchange = function () {
            if (this.files && this.files[0]) {
                const formData = new FormData();
                formData.append('action', 'upload_background');
                formData.append('entity', 'table');
                formData.append('image', this.files[0]);
                appendCsrf(formData);

                uploadFloorPlanBtn.innerText = "Uploading...";
                uploadFloorPlanBtn.disabled = true;

                fetch('controllers/admin_handler.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(r => r.json())
                    .then(data => {
                        uploadFloorPlanBtn.innerText = "Upload Floor Plan";
                        uploadFloorPlanBtn.disabled = false;
                        if (data.success) {
                            loadTables(); // Reload to fetch new bg
                            alert("Background updated!");
                        } else {
                            alert("Error: " + data.error);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        uploadFloorPlanBtn.disabled = false;
                        alert("Upload failed.");
                    });
            }
        };
    }

    // Table Properties Modal Logic
    const tablePropsModal = document.getElementById('table-props-modal');
    const tablePropsForm = document.getElementById('table-props-form');

    if (tablePropsForm) {
        tablePropsForm.onsubmit = function (e) {
            e.preventDefault();
            const id = document.getElementById('prop-table-id').value;
            const shape = document.getElementById('prop-shape').value;
            const width = document.getElementById('prop-width').value;
            const height = document.getElementById('prop-height').value;

            postAction({
                action: 'update_details',
                entity: 'table',
                id: id,
                shape: shape,
                width: width,
                height: height
            }, () => {
                if (tablePropsModal) tablePropsModal.style.display = 'none';
                loadTables();
            });
        };
    }

    function loadTables() {
        console.log("Fetching tables...");
        const container = document.getElementById('tables-grid');
        if (!container) return;

        container.innerHTML = '<p>Loading tables...</p>';

        const formData = new FormData();
        formData.append('action', 'get_all');
        formData.append('entity', 'table');
        appendCsrf(formData);

        fetch('controllers/admin_handler.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    currentTables = data.data || [];
                    renderTables(data.data, data.background);
                    const countDisplay = document.getElementById('table-count-display');
                    if (countDisplay && data.data) {
                        countDisplay.textContent = data.data.length;
                    }
                } else {
                    container.innerHTML = '<p>Error loading tables.</p>';
                }
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<p>Connection Error.</p>';
            });
    }

    function renderTables(tables, backgroundUrl) {
        const gridContainer = document.getElementById('tables-grid');
        const floorPlan = document.getElementById('floor-plan-container');

        if (!gridContainer || !floorPlan) return;

        gridContainer.innerHTML = '';
        floorPlan.innerHTML = '<p style="position: absolute; top: 10px; left: 10px; z-index:0; color: #888; pointer-events: none;">Floor Plan Area</p>';

        if (backgroundUrl) {
            // Load image to get dimensions
            const img = new Image();
            img.onload = function () {
                floorPlan.style.backgroundImage = `url('${backgroundUrl}')`;
                floorPlan.style.backgroundSize = 'contain'; // or cover, does not matter if ratio matches
                floorPlan.style.backgroundRepeat = 'no-repeat';
                floorPlan.style.backgroundPosition = 'center';

                // Apply Aspect Ratio
                const ratio = img.width / img.height;
                // Set height based on current width to match ratio
                // floorPlan.style.height = (floorPlan.offsetWidth / ratio) + 'px'; // Simple js resize
                floorPlan.style.height = 'auto';
                floorPlan.style.aspectRatio = `${img.width} / ${img.height}`;
            };
            img.src = backgroundUrl;

        } else {
            floorPlan.style.backgroundImage = 'radial-gradient(#ccc 1px, transparent 1px)';
            floorPlan.style.backgroundSize = '20px 20px';
            floorPlan.style.height = '600px'; // Default
            floorPlan.style.aspectRatio = 'auto';
        }

        if (!tables || tables.length === 0) {
            gridContainer.innerHTML = '<p>No tables found.</p>';
            return;
        }

        tables.forEach(t => {
            // 1. Grid Item (Control Status)
            const card = document.createElement('div');

            let displayStatus = t.Status;
            let icon = 'fa-chair';
            let extraInfo = '';

            if (t.active_reservation) {
                displayStatus = 'Rezervata';
                icon = 'fa-clock';
                // Show Hour:Minute
                const timeShort = t.active_reservation.reservation_time.split(' ')[1].substring(0, 5);
                extraInfo = `<div style="font-size: 0.75rem; color: #d35400; font-weight: bold; margin-bottom: 5px; background: rgba(255,255,255,0.8); padding: 2px; border-radius: 4px;">
                                ${timeShort} - ${t.active_reservation.username}
                              </div>`;
            } else {
                if (t.Status === 'Ocupata') {
                    icon = 'fa-user-friends';
                    extraInfo = `<div style="font-size: 0.75rem; color: #c0392b; font-weight: bold; margin-bottom: 5px; background: rgba(255,255,255,0.8); padding: 2px; border-radius: 4px;">Manual: Occupied</div>`;
                }
                if (t.Status === 'Rezervata') {
                    icon = 'fa-clock';
                    extraInfo = `<div style="font-size: 0.75rem; color: #f39c12; font-weight: bold; margin-bottom: 5px; background: rgba(255,255,255,0.8); padding: 2px; border-radius: 4px;">Manual: Reserved</div>`;
                }
                if (t.Status === 'Inactiva') {
                    icon = 'fa-ban';
                    extraInfo = `<div style="font-size: 0.75rem; color: #7f8c8d; font-weight: bold; margin-bottom: 5px; background: rgba(255,255,255,0.8); padding: 2px; border-radius: 4px;">Inactive</div>`;
                }
            }

            const statusClass = 'status-' + displayStatus.toLowerCase().replace(/\s+/g, '-');
            card.className = `table-card ${statusClass}`;
            card.style.flex = '1 0 200px';

            card.innerHTML = `
                <div class="table-icon"><i class="fas ${icon}"></i></div>
                <div class="table-id">Table ${t.ID}</div>
                ${extraInfo}
                <div style="font-size: 0.8rem; color: #666; margin-bottom: 5px;">${t.shape || 'circle'}</div>
                <button class="btn btn-sm btn-secondary" onclick="openTableProps(${t.ID})" style="margin-bottom: 5px;">Edit Props</button>
                <select class="table-status-select" onchange="window.updateTableStatus(${t.ID}, this.value)">
                    <option value="Libera" ${t.Status === 'Libera' ? 'selected' : ''}>Libera</option>
                    <option value="Ocupata" ${t.Status === 'Ocupata' ? 'selected' : ''}>Ocupata</option>
                    <option value="Rezervata" ${t.Status === 'Rezervata' ? 'selected' : ''}>Rezervata</option>
                    <option value="Inactiva" ${t.Status === 'Inactiva' ? 'selected' : ''}>Inactiva</option>
                </select>
            `;
            gridContainer.appendChild(card);

            // 2. Floor Plan Item (Draggable)
            const mapItem = document.createElement('div');
            mapItem.className = `map-table ${statusClass}`;
            mapItem.id = `map-table-${t.ID}`;
            mapItem.style.position = 'absolute';
            mapItem.style.left = (t.x_pos || 10) + '%';
            mapItem.style.top = (t.y_pos || 10) + '%';

            const w = t.width || 60;
            const h = t.height || 60;
            mapItem.style.width = w + 'px';
            mapItem.style.height = h + 'px';

            const shape = t.shape || 'circle';
            if (shape === 'circle') mapItem.style.borderRadius = '50%';
            else if (shape === 'square') mapItem.style.borderRadius = '8px';
            else if (shape === 'rectangle') mapItem.style.borderRadius = '8px';

            mapItem.style.border = '2px solid #333';
            mapItem.style.display = 'flex';
            mapItem.style.alignItems = 'center';
            mapItem.style.justifyContent = 'center';
            mapItem.style.fontWeight = 'bold';
            mapItem.style.color = '#fff';
            mapItem.style.cursor = 'grab';
            mapItem.style.boxShadow = '0 2px 5px rgba(0,0,0,0.3)';

            // Dynamic Status Override for Admin Map
            if (t.active_reservation) {
                mapItem.style.backgroundColor = '#f1c40f'; // Yellow
                mapItem.style.border = '3px solid #e67e22'; // Distinct border
                mapItem.title = `Table ${t.ID}\nRESERVED\nUser: ${t.active_reservation.username}\nTime: ${t.active_reservation.reservation_time}`;
                // Add icon or text?
                mapItem.innerHTML = `<div style="display:flex;flex-direction:column;pointer-events:none;align-items:center;"><i class="fas fa-clock"></i><span>${t.ID}</span></div>`;
            } else {
                mapItem.style.backgroundColor = getStatusColor(t.Status);
                mapItem.title = `Table ${t.ID}: ${t.Status}`;
                mapItem.innerHTML = `<div style="pointer-events: none;">${t.ID}</div>`;
            }

            mapItem.style.zIndex = '10';

            // Mouse Move for Cursor Change
            mapItem.onmousemove = function (e) {
                if (draggedTableId || resizingTableId) return;

                const rect = mapItem.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const w = rect.width;
                const h = rect.height;
                const margin = 10;

                let cursor = '';
                let n = y < margin;
                let s = y > h - margin;
                let w_side = x < margin;
                let e_side = x > w - margin;

                if (n && w_side) cursor = 'nw-resize';
                else if (n && e_side) cursor = 'ne-resize';
                else if (s && w_side) cursor = 'sw-resize';
                else if (s && e_side) cursor = 'se-resize';
                else if (n) cursor = 'n-resize';
                else if (s) cursor = 's-resize';
                else if (w_side) cursor = 'w-resize';
                else if (e_side) cursor = 'e-resize';
                else cursor = 'grab';

                mapItem.style.cursor = cursor;
                mapItem.dataset.resizeDir = cursor === 'grab' ? '' : cursor.replace('-resize', '');
            };

            // Mouse Down for Action
            mapItem.onmousedown = function (e) {
                if (e.button !== 0) return; // Only left click
                const dir = mapItem.dataset.resizeDir;
                if (dir) {
                    initResize(e, t.ID, dir);
                } else {
                    startDrag(e, t.ID);
                }
            };

            mapItem.ondblclick = () => openTableProps(t.ID);

            floorPlan.appendChild(mapItem);
        });
    }

    // --- Resize Logic ---
    let resizingTableId = null;
    let resizeDir = '';
    let startX, startY, startWidth, startHeight, startLeft, startTop;

    function initResize(e, id, dir) {
        e.stopPropagation();
        e.preventDefault();
        resizingTableId = id;
        resizeDir = dir;

        const tableEl = document.getElementById(`map-table-${id}`);
        const rect = tableEl.getBoundingClientRect();

        startX = e.clientX;
        startY = e.clientY;
        startWidth = rect.width;
        startHeight = rect.height;
        startLeft = parseFloat(tableEl.style.left); // % value
        startTop = parseFloat(tableEl.style.top);   // % value

        // Convert % positions to pixels for calc
        const container = document.getElementById('floor-plan-container');
        const conRect = container.getBoundingClientRect();
        startLeftPx = (startLeft / 100) * conRect.width;
        startTopPx = (startTop / 100) * conRect.height;

        document.documentElement.addEventListener('mousemove', doResize, false);
        document.documentElement.addEventListener('mouseup', stopResize, false);
    }

    function doResize(e) {
        if (!resizingTableId) return;
        e.preventDefault();

        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;
        const tableEl = document.getElementById(`map-table-${resizingTableId}`);
        const container = document.getElementById('floor-plan-container');
        const conW = container.offsetWidth;
        const conH = container.offsetHeight;

        let newW = startWidth;
        let newH = startHeight;
        let newLeftPx = startLeftPx;
        let newTopPx = startTopPx;

        // Apply Delta based on direction
        if (resizeDir.includes('e')) newW = startWidth + deltaX;
        if (resizeDir.includes('w')) {
            newW = startWidth - deltaX;
            newLeftPx = startLeftPx + deltaX;
        }
        if (resizeDir.includes('s')) newH = startHeight + deltaY;
        if (resizeDir.includes('n')) {
            newH = startHeight - deltaY;
            newTopPx = startTopPx + deltaY;
        }

        // Min dimensions
        if (newW < 30) {
            // Correct Left if scaling from West
            if (resizeDir.includes('w')) newLeftPx = startLeftPx + (startWidth - 30);
            newW = 30;
        }
        if (newH < 30) {
            // Correct Top if scaling from North
            if (resizeDir.includes('n')) newTopPx = startTopPx + (startHeight - 30);
            newH = 30;
        }

        // Update Styles
        tableEl.style.width = newW + 'px';
        tableEl.style.height = newH + 'px';

        // Only update pos if it changed (optimization)
        if (resizeDir.includes('w') || resizeDir.includes('n')) {
            tableEl.style.left = (newLeftPx / conW * 100) + '%';
            tableEl.style.top = (newTopPx / conH * 100) + '%';
        }
    }

    function stopResize(e) {
        if (!resizingTableId) return;

        const tableEl = document.getElementById(`map-table-${resizingTableId}`);
        const container = document.getElementById('floor-plan-container');

        // Update data
        const t = currentTables.find(x => x.ID == resizingTableId);
        if (t) {
            t.width = parseInt(tableEl.style.width);
            t.height = parseInt(tableEl.style.height);
            // Also need to save position if we resized from West/North
            t.x_pos = parseFloat(tableEl.style.left);
            t.y_pos = parseFloat(tableEl.style.top);

            // Mark these specifically as changed
            if (!window.unsavedChanges) window.unsavedChanges = {};
            window.unsavedChanges[resizingTableId] = {
                x: t.x_pos,
                y: t.y_pos,
                width: t.width,
                height: t.height
            };

            // HACK: Since standard save logic might only look at style.left/top, 
            // we should attach W/H to the object if our save function supports it. 
            // Or just rely on the 'update_details' endpoint?
            // Actually, best to trigger a dedicated update for size OR piggyback on save.
            // Let's assume piggyback for now, but I will check save logic next tool call.
        }

        document.documentElement.removeEventListener('mousemove', doResize, false);
        document.documentElement.removeEventListener('mouseup', stopResize, false);
        resizingTableId = null;

        // UI Feedback
        const btn = document.getElementById('save-positions-btn');
        btn.textContent = "Save Changes *";
        btn.classList.add('btn-warning');
        btn.classList.remove('btn-success');
    }

    // --- Drag Logic ---
    let draggedTableId = null;
    let dragOffsetX = 0;
    let dragOffsetY = 0;

    // Helper to calculate px pos from %
    let startLeftPx, startTopPx;

    function startDrag(e, id) {
        e.preventDefault();
        const el = document.getElementById(`map-table-${id}`);
        draggedTableId = id;

        // Calculate offset from top-left of element
        const rect = el.getBoundingClientRect();
        dragOffsetX = e.clientX - rect.left;
        dragOffsetY = e.clientY - rect.top;

        el.style.cursor = 'grabbing';
        el.style.zIndex = '100';

        document.documentElement.addEventListener('mousemove', doDrag, false);
        document.documentElement.addEventListener('mouseup', stopDrag, false);
    }

    function doDrag(e) {
        if (!draggedTableId) return;
        e.preventDefault();

        const el = document.getElementById(`map-table-${draggedTableId}`);
        const floorPlan = document.getElementById('floor-plan-container');
        const containerRect = floorPlan.getBoundingClientRect();

        let newX = e.clientX - containerRect.left - dragOffsetX;
        let newY = e.clientY - containerRect.top - dragOffsetY;

        // Clamp inside container (pixels)
        const maxX = containerRect.width - el.offsetWidth;
        const maxY = containerRect.height - el.offsetHeight;

        newX = Math.max(0, Math.min(newX, maxX));
        newY = Math.max(0, Math.min(newY, maxY));

        // Convert to percentage for storage
        const percentX = (newX / containerRect.width) * 100;
        const percentY = (newY / containerRect.height) * 100;

        // Update visual mostly in px for smoothness, or %? 
        // Using % directly is fine if simple.
        el.style.left = percentX + '%';
        el.style.top = percentY + '%';

        // Store temp percentage for save
        // This is a bit redundant with activeDrag.percentX/Y, but keeping for consistency with new structure
        const t = currentTables.find(x => x.ID == draggedTableId);
        if (t) {
            t.x_pos = percentX;
            t.y_pos = percentY;
        }
    }

    function stopDrag(e) {
        if (!draggedTableId) return;

        const el = document.getElementById(`map-table-${draggedTableId}`);
        el.style.cursor = 'grab';
        el.style.zIndex = '10';

        const t = currentTables.find(x => x.ID == draggedTableId);
        if (t && t.x_pos !== undefined && t.y_pos !== undefined) {
            if (!window.unsavedChanges) window.unsavedChanges = {}; // Safety init if not present yet
            window.unsavedChanges[draggedTableId] = {
                x: t.x_pos,
                y: t.y_pos,
                width: t.width || 60, // Ensure we send current size too
                height: t.height || 60
            };
        }

        draggedTableId = null;
        document.documentElement.removeEventListener('mousemove', doDrag, false);
        document.documentElement.removeEventListener('mouseup', stopDrag, false);

        // UI Feedback
        const btn = document.getElementById('save-positions-btn');
        btn.textContent = "Save Changes *";
        btn.classList.add('btn-warning');
        btn.classList.remove('btn-success');
    }

    window.openTableProps = function (id) {
        const t = currentTables.find(x => x.ID == id);
        if (!t) return;

        document.getElementById('prop-table-id').value = t.ID;
        document.getElementById('prop-shape').value = t.shape || 'circle';
        document.getElementById('prop-width').value = t.width || 60;
        document.getElementById('prop-height').value = t.height || 60;

        if (tablePropsModal) tablePropsModal.style.display = 'block';
    }

    function getStatusColor(status) {
        switch (status) {
            case 'Libera': return '#2ecc71';
            case 'Ocupata': return '#e74c3c';
            case 'Rezervata': return '#f1c40f';
            case 'Inactiva': return '#95a5a6';
            default: return '#95a5a6';
        }
    }

    // Global storage for unsaved changes
    window.unsavedChanges = {};

    // Save Positions Button Logic
    const savePositionsBtn = document.getElementById('save-positions-btn');
    if (savePositionsBtn) {
        savePositionsBtn.onclick = function () {
            const ids = Object.keys(window.unsavedChanges);
            if (ids.length === 0) {
                alert("No changes to save.");
                return;
            }

            savePositionsBtn.innerText = "Saving...";
            savePositionsBtn.disabled = true;

            let promises = [];
            ids.forEach(id => {
                const pos = window.unsavedChanges[id];
                const p = new Promise((resolve) => {
                    // Manually fetch to handle errors properly
                    const formData = new FormData();
                    formData.append('action', 'update_coordinates');
                    formData.append('entity', 'table');
                    formData.append('id', id);
                    formData.append('x', pos.x);
                    formData.append('y', pos.y);
                    formData.append('width', pos.width || 60);
                    formData.append('height', pos.height || 60);
                    appendCsrf(formData);

                    fetch('controllers/admin_handler.php', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (!data.success) {
                                console.error(`Failed to save table ${id}: ${data.error}`);
                            }
                        })
                        .catch(err => {
                            console.error(`Network error saving table ${id}`, err);
                        })
                        .finally(() => {
                            resolve(); // Always resolve so Promise.all completes
                        });
                });
                promises.push(p);
            });

            Promise.all(promises).then(() => {
                window.unsavedChanges = {};
                savePositionsBtn.innerText = "Save Positions";
                savePositionsBtn.disabled = false;
                alert("Positions processed (check console for any errors).");
            });
        };
    }



    window.updateTableStatus = function (id, newStatus) {
        postAction({ action: 'update_status', entity: 'table', id: id, status: newStatus }, () => {
            // Optional: reload tables to reflect changes fully or just update UI class
            loadTables();
        });
    };

    // Initial Load Logic - Ensure Active State
    const activeLink = document.querySelector('.sidebar-nav .nav-link.active');
    if (!activeLink) {
        // Default to Dashboard if none active
        const dashboardLink = document.querySelector('.sidebar-nav .nav-link[data-section="dashboard"]');
        if (dashboardLink) dashboardLink.classList.add('active');
        // Ensure Dashboard section is shown
        const dashboardSection = document.getElementById('section-dashboard');
        if (dashboardSection) {
            sections.forEach(s => s.style.display = 'none');
            dashboardSection.style.display = 'block';
        }
    } else {
        const sectionId = activeLink.getAttribute('data-section');
        if (sectionId) {
            sections.forEach(s => s.style.display = 'none');
            const sec = document.getElementById('section-' + sectionId);
            if (sec) sec.style.display = 'block';
        }
    }

    // Check data loading based on potential active section
    if (document.getElementById('section-menu').style.display !== 'none') {
        loadProducts();
    } else if (document.getElementById('section-slider').style.display !== 'none') {
        loadSliderImages();
    } else if (document.getElementById('section-tables').style.display !== 'none') {
        loadTables();
    } else if (document.getElementById('section-reservations').style.display !== 'none') {
        loadReservations();
    }

    // Reservations Logic
    function loadReservations() {
        const tbodyActive = document.getElementById('reservations-list');
        const tbodyHistory = document.getElementById('history-reservations-list');

        tbodyActive.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
        if (tbodyHistory) tbodyHistory.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';

        postAction({ action: 'get_all', entity: 'reservation' }, (response) => {
            if (!response || !response.success) {
                const err = `<tr><td colspan="5">Error: ${response ? (response.message || response.error) : 'Unknown'}</td></tr>`;
                tbodyActive.innerHTML = err;
                if (tbodyHistory) tbodyHistory.innerHTML = `<tr><td colspan="4">Error loading data.</td></tr>`;
                return;
            }
            if (!response.data || response.data.length === 0) {
                tbodyActive.innerHTML = '<tr><td colspan="5">No active reservations.</td></tr>';
                if (tbodyHistory) tbodyHistory.innerHTML = '<tr><td colspan="4">No past reservations.</td></tr>';
                return;
            }

            const now = new Date();
            const active = [];
            const past = [];

            response.data.forEach(r => {
                // Ensure date string is parseable (SQL format YYYY-MM-DD HH:MM:SS -> ISOish)
                const rTime = new Date(r.reservation_time.replace(' ', 'T'));
                if (rTime < now) {
                    past.push(r);
                } else {
                    active.push(r);
                }
            });

            // Active: Ascending (Sooner first)
            active.sort((a, b) => new Date(a.reservation_time) - new Date(b.reservation_time));
            // Past: Descending (Most recent history first)
            past.sort((a, b) => new Date(b.reservation_time) - new Date(a.reservation_time));

            const renderRow = (r, isHistory) => {
                const isBlocked = parseInt(r.is_blacklisted) === 1;
                const btnText = isBlocked ? 'Unblacklist' : 'Blacklist';

                let cols = `
                    <td>${r.reservation_time}</td>
                    <td>Table ${r.table_id}</td>
                    <td><strong>${safe(r.reservation_name || 'N/A')}</strong></td>
                    <td>${r.username}</td>
                    <td>${r.email}</td>
                    <td>
                        <button class="btn ${isBlocked ? 'btn-success' : 'btn-warning'} btn-sm" onclick="window.toggleBlacklist(${r.user_id}, ${isBlocked})" title="${btnText} User" style="margin-right:5px;">
                            <i class="fas fa-${isBlocked ? 'check' : 'ban'}"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="window.deleteReservation(${r.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                return `<tr>${cols}</tr>`;
            };

            // Render Active
            if (active.length === 0) {
                tbodyActive.innerHTML = '<tr><td colspan="5">No active or upcoming reservations.</td></tr>';
            } else {
                tbodyActive.innerHTML = active.map(r => renderRow(r, false)).join('');
            }

            // Render History
            if (tbodyHistory) {
                if (past.length === 0) {
                    tbodyHistory.innerHTML = '<tr><td colspan="4">No past history found.</td></tr>';
                } else {
                    tbodyHistory.innerHTML = past.map(r => renderRow(r, true)).join('');
                }
            }
        });
    }

    window.toggleBlacklist = function (userId, isBlocked) {
        let reason = '';
        if (!isBlocked) {
            // Currently NOT blocked, so we are blocking. Reason required.
            reason = prompt("Please provide a reason for blacklisting this user:");
            if (reason === null) return; // Cancelled
            reason = reason.trim();
            if (!reason) {
                alert("A reason is required to blacklist a user.");
                return;
            }
        } else {
            if (!confirm("Are you sure you want to UNBLACKLIST this user?")) return;
        }

        postAction({
            action: 'toggle_blacklist',
            entity: 'reservation',
            user_id: userId,
            reason: reason
        }, () => {
            loadReservations(); // Reload list
        });
    };

    window.deleteReservation = function (resId) {
        if (!confirm("Are you sure you want to DELETE this reservation?")) return;

        postAction({
            action: 'delete',
            entity: 'reservation',
            id: resId
        }, () => {
            loadReservations(); // Reload list
            loadTables(); // Reload tables too to update colors
        });
    };

})();
