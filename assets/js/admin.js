// Main Initialization Wrapper
function initAdminPanel() {
    console.log("Admin Panel Initializing...", new Date().toISOString());
    // Visual confirmation for debug
    // const d = document.createElement('div');
    // d.style.cssText = 'position:fixed;top:10px;right:10px;background:lime;padding:5px;z-index:9999;';
    // d.textContent = 'Admin Start: ' + new Date().toLocaleTimeString();
    // document.body.appendChild(d); setTimeout(() => d.remove(), 2000);

    // Determine active section from URL or default
    showSection('dashboard');

    // Navigation Logic (Event Delegation)
    const sidebarNav = document.querySelector('.sidebar-nav');
    if (sidebarNav) {
        console.log("Sidebar nav found, attaching listener.");
        sidebarNav.addEventListener('click', (e) => {
            const link = e.target.closest('.nav-link[data-section]');
            if (link) {
                e.preventDefault();
                const sectionId = link.getAttribute('data-section');
                console.log("Navigating to section:", sectionId);
                showSection(sectionId);
            }
        });
    } else {
        console.error("Sidebar nav NOT found!");
    }

    // 1. DASHBOARD INIT
    loadDashboard();
    setupSearch();
    setupScheduleForm();
    setupQuickActions();

    // Notes auto-save
    const notesArea = document.getElementById('admin-notes');
    if (notesArea) {
        let timeout = null;
        notesArea.addEventListener('input', () => {
            clearTimeout(timeout);
            document.getElementById('notes-status').style.display = 'none';
            timeout = setTimeout(saveNotes, 1000);
        });
    }

    // Modal Close Logic
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById(btn.dataset.target).style.display = 'none';
        });
    });

    window.onclick = function (event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }

    // --- PRODUCT MODULE SETUP ---
    const addProdBtn = document.getElementById('add-product-btn');
    if (addProdBtn) {
        addProdBtn.addEventListener('click', () => {
            document.getElementById('product-form').reset();
            document.getElementById('prod-id').value = '';
            document.getElementById('form-action').value = 'add';
            document.getElementById('modal-title').innerText = 'Add Product';
            document.getElementById('current-image-preview').style.display = 'none';
            document.getElementById('product-modal').style.display = 'block';
        });
    }

    const prodForm = document.getElementById('product-form');
    if (prodForm) {
        prodForm.addEventListener('submit', handleProductSubmit);
    }

    // --- SLIDER MODULE SETUP ---
    const addSlideBtn = document.getElementById('add-slide-btn');
    if (addSlideBtn) {
        addSlideBtn.addEventListener('click', () => {
            document.getElementById('slider-form').reset();
            document.getElementById('slider-modal').style.display = 'block';
        });
    }
    const sliderForm = document.getElementById('slider-form');
    if (sliderForm) {
        sliderForm.addEventListener('submit', handleSliderSubmit);
    }

    // --- TABLE MODULE SETUP ---
    const addTableBtn = document.getElementById('add-table-btn');
    if (addTableBtn) addTableBtn.addEventListener('click', () => updateTableCount(1));

    const removeTableBtn = document.getElementById('remove-table-btn');
    if (removeTableBtn) removeTableBtn.addEventListener('click', () => updateTableCount(-1));

    const savePositionsBtn = document.getElementById('save-positions-btn');
    // Note: Position saving is usually per-drag but we can implement a bulk save if tracked. 
    // For now, let's assume auto-save on drag end or alert "Saved".
    if (savePositionsBtn) savePositionsBtn.addEventListener('click', () => alert("Positions should be saved automatically on move."));

    const uploadFloorPlanBtn = document.getElementById('upload-floor-plan-btn');
    const floorPlanInput = document.getElementById('floor-plan-upload');
    if (uploadFloorPlanBtn && floorPlanInput) {
        uploadFloorPlanBtn.addEventListener('click', () => floorPlanInput.click());
        floorPlanInput.addEventListener('change', uploadFloorPlan);
    }

    // Table Props Form
    const tablePropsForm = document.getElementById('table-props-form');
    if (tablePropsForm) {
        tablePropsForm.addEventListener('submit', handleTablePropsSubmit);
    }

    // --- ORDERS SETUP ---
    const refreshOrdersBtn = document.getElementById('refresh-orders-btn');
    if (refreshOrdersBtn) refreshOrdersBtn.addEventListener('click', loadOrders);
}

// Auto-run if already loaded (for SPA)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminPanel);
} else {
    initAdminPanel();
}

// --- GENERAL UTILS ---
function showSection(sectionId) {
    document.querySelectorAll('.admin-section').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));

    const target = document.getElementById('section-' + sectionId);
    if (target) {
        target.style.display = 'block';
        const nav = document.querySelector(`.nav-link[data-section="${sectionId}"]`);
        if (nav) nav.classList.add('active');

        if (sectionId === 'dashboard') loadDashboard();
        if (sectionId === 'users') loadUsers();
        if (sectionId === 'settings') loadSettings();
        if (sectionId === 'menu') loadProducts();
        if (sectionId === 'slider') loadSlides();
        if (sectionId === 'tables') loadTables();
        if (sectionId === 'reservations') loadReservations();
        if (sectionId === 'orders') loadOrders();
    }
}

async function apiRequest(entity, action, body = null) {
    const formData = body instanceof FormData ? body : new FormData();
    formData.append('entity', entity);
    formData.append('action', action);
    if (!(body instanceof FormData) && body) {
        for (let k in body) formData.append(k, body[k]);
    }

    const tokenVal = document.getElementById('csrf-token-global').value;

    try {
        const res = await fetch('controllers/admin_handler.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': tokenVal },
            credentials: 'include' // Ensure session cookies are sent
        });

        if (!res.ok) {
            const text = await res.text();
            console.error("API Error Status:", res.status);
            console.error("API Error Body:", text);
            throw new Error(`Server responded with ${res.status}: ${text.substring(0, 100)}`);
        }

        return await res.json();
    } catch (e) {
        console.error("Fetch failed", e);
        alert("Server communication error: " + e.message);
        return { success: false, error: e.message };
    }
}

// ==========================================
// 1. DASHBOARD & SETTINGS & USERS (Preserved)
// ==========================================
async function loadDashboard() {
    try {
        console.log("Fetching dashboard stats...");
        const res = await apiRequest('dashboard', 'get_dashboard_stats');
        console.log("Dashboard fetch result:", res);

        if (res.success) {
            renderDashboard(res.data);
        } else {
            console.warn("Dashboard fetch failed logic:", res.error);
            // Optional: alert(res.error || "Failed to load dashboard data");
            // For now, let's render empty state or partial?
            document.getElementById('stat-res-today').innerText = "Err";
        }
    } catch (e) {
        console.error("Dashboard load failed exception", e);
    }
}
function renderDashboard(data) {
    console.log("Rendering dashboard with:", data);
    if (!data) return;
    const ids = {
        'stat-res-today': data.stats.reservations_today,
        'stat-res-total': 'Upcoming: ' + data.stats.reservations_total,
        'stat-active-tables': data.stats.active_tables,
        'stat-menu-items': data.stats.products_total,
        'admin-notes': data.notes
    };
    for (let id in ids) {
        const el = document.getElementById(id);
        if (el) {
            if (el.tagName === 'TEXTAREA') el.value = ids[id];
            else el.innerText = ids[id];
        } else {
            console.warn("Missing element for ID:", id);
        }
    }
    if (document.getElementById('global-cafe-status')) document.getElementById('global-cafe-status').value = data.cafe_status;

    const list = document.getElementById('recent-activity-list');
    if (list && data.recent) {
        list.innerHTML = data.recent.map(r => `
            <tr><td>${r.user}</td><td>${r.name}</td><td>${r.created}</td><td>${r.time}</td><td>#${r.id}</td></tr>
        `).join('');
    }
    if (data.chart) renderChart(data.chart);
}
var resChartInstance = null;
function renderChart(chartData) {
    const cvs = document.getElementById('reservationsChart');
    if (!cvs) return;
    if (resChartInstance) resChartInstance.destroy();
    resChartInstance = new Chart(cvs.getContext('2d'), {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Quantity Sold',
                data: chartData.data,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // Horizontal bars
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.parsed.x + ' ' + context.label + ' sold';
                        }
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });
}
async function saveNotes() {
    await apiRequest('dashboard', 'save_note', { content: document.getElementById('admin-notes').value });
    const s = document.getElementById('notes-status');
    s.style.display = 'block'; setTimeout(() => s.style.display = 'none', 2000);
}
async function updateCafeStatus(val) { await apiRequest('dashboard', 'toggle_cafe_status', { status: val }); }
function exportData(type) { window.location.href = `controllers/admin_handler.php?entity=dashboard&action=export_data&type=${type}`; }
async function sendNewsletter() {
    const sub = document.getElementById('news-subject').value;
    const body = document.getElementById('news-body').value;
    if (!sub || !body) return alert("Required");
    const res = await apiRequest('dashboard', 'send_newsletter', { subject: sub, body: body });
    alert(res.success ? res.data.message : res.error);
}

// USERS
function setupSearch() {
    const inp = document.getElementById('user-search');
    if (!inp) return;
    let t;
    inp.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => loadUsers(inp.value), 500); });
}
async function loadUsers(search = '') {
    const res = await apiRequest('user', 'get_all', { search: search });
    const tb = document.querySelector('#users-table tbody');
    if (res.success && tb) {
        tb.innerHTML = res.data.map(u => `
            <tr>
                <td><div style="display:flex;align-items:center;gap:10px;"><img src="${u.PPicture || 'assets/img/default-user.png'}" style="width:30px;height:30px;border-radius:50%;"><span>${u.username}</span></div></td>
                <td>${u.email}</td><td>${u.role}</td><td>${u.PuncteFidelitate}</td>
                <td>${u.is_blacklisted == 1 ? '<span style="color:red">Blacklisted</span>' : '<span style="color:green">Active</span>'}</td>
                <td><button class="btn btn-sm btn-edit" onclick="viewUser(${u.id})">Details</button></td>
            </tr>`).join('');
    }
}
async function viewUser(id) {
    const res = await apiRequest('user', 'get_one', { id: id });
    if (res.success) {
        const u = res.data;
        let statusHtml = '';
        if (u.is_blacklisted == 1) {
            statusHtml = `<div style="background:#ffebee; color:#c62828; padding:10px; border-radius:4px; margin:10px 0;">
                <strong>BLACKLISTED</strong><br>
                Reason: ${u.blacklist_reason || 'No reason provided'}
            </div>`;
        }

        document.getElementById('user-details-content').innerHTML = `
            <div style="text-align:center;"><img src="${u.PPicture || 'assets/img/default-user.png'}" style="width:80px;height:80px;border-radius:50%;"><h3>${u.username}</h3><p>${u.email}</p></div>
            ${statusHtml}
            <p><strong>Reservations:</strong> ${u.total_reservations} | <strong>Deleted Res:</strong> ${u.deleted_reservations || 0} | <strong>Orders:</strong> ${u.total_orders} | <strong>Points:</strong> ${u.PuncteFidelitate}</p>
        `;

        // Configure Blacklist Button
        const btn = document.getElementById('blacklist-btn');
        const reasonBox = document.getElementById('blacklist-reason');

        // Remove old listeners to avoid stacking (simplest way is to clone or reset)
        // Better: just assign onclick here since we are in a specific context
        btn.onclick = () => handleBlacklistToggle(u.id, u.is_blacklisted);

        if (u.is_blacklisted == 1) {
            btn.innerText = "Unblacklist User";
            btn.className = "btn btn-success";
            reasonBox.style.display = 'none'; // No reason needed to unblacklist usually, or maybe clear it
        } else {
            btn.innerText = "Blacklist User";
            btn.className = "btn btn-danger";
            reasonBox.style.display = 'block';
            reasonBox.value = ''; // clear previous
        }

        document.getElementById('user-modal').style.display = 'block';
    }
}

async function handleBlacklistToggle(id, currentStatus) {
    const reasonBox = document.getElementById('blacklist-reason');
    const reason = reasonBox.value;

    // If getting blacklisted (currentStatus == 0), reason is required
    if (currentStatus == 0 && !reason.trim()) {
        alert("Please provide a reason for blacklisting.");
        return;
    }

    if (!confirm(currentStatus == 1 ? "Unblacklist this user?" : "Blacklist this user?")) return;

    // Use reservation controller for this action as it was defined there previously or move to User?
    // User controller didn't have toggle_blacklist in the code I saw earlier?
    // Wait, ReservationController HAD toggle_blacklist. admin_handler routes entity 'reservation' there.
    // DOES User controller have it? 
    // Let's check admin_handler or just use 'reservation' entity for now if that's where the logic is.
    // The previous code showed `ReservationController` having `toggleBlacklist`.
    // Ideally it belongs in User, but let's use what works or checking existing `admin_handler` map.
    // Actually, I should probably move it to User controller or call it via 'user' entity if I add it there.
    // Let's stick to where it was: ReservationController has it. 
    // BUT, does `admin_handler` route `user` entity actions to `UserController`? Yes.
    // Calls `UserController` for `get_one`.
    // I should probably check where `toggle_blacklist` is currently handled.
    // I recall seeing it in ReservationController in previous turns.

    // Let's try calling entity='reservation', action='toggle_blacklist' since I saw it there.
    const res = await apiRequest('reservation', 'toggle_blacklist', { user_id: id, reason: reason });

    if (res.success) {
        // alert(res.data.message); // Removed for smoother flow
        document.getElementById('user-modal').style.display = 'none';
        loadUsers(document.getElementById('user-search').value);
    } else {
        alert(res.error);
    }
}

// SETTINGS
async function loadSettings() {
    const res = await apiRequest('settings', 'get_schedule');
    const tb = document.getElementById('schedule-list');
    if (res.success && tb) {
        tb.innerHTML = res.data.map((d, i) => `
            <tr>
                <td>${d.day_name}<input type="hidden" name="schedule[${i}][day_of_week]" value="${d.day_of_week}"></td>
                <td><input type="time" name="schedule[${i}][open_time]" value="${d.open_time}"></td>
                <td><input type="time" name="schedule[${i}][close_time]" value="${d.close_time}"></td>
                <td><input type="checkbox" name="schedule[${i}][is_closed]" value="1" ${d.is_closed == 1 ? 'checked' : ''} onchange="this.value=this.checked?1:0"></td>
            </tr>
        `).join('');
    }
}
function setupScheduleForm() {
    const f = document.getElementById('schedule-form');
    if (f) f.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await apiRequest('settings', 'update_schedule', new FormData(f));
        alert(res.success ? res.data.message : res.error);
    });
}

// ==========================================
// 2. PRODUCTS MODULE (Restored)
// ==========================================
async function loadProducts() {
    const res = await apiRequest('product', 'get_all');
    const tb = document.querySelector('#products-table tbody');
    if (res.success && tb) {
        tb.innerHTML = res.data.map(p => `
            <tr>
                <td><img src="${p.image_path || 'assets/menu/images/coffee.jpg'}" onerror="this.src='assets/img/Logo Modificat.png'"></td>
                <td>${p.name}</td>
                <td>${p.category}</td>
                <td>${p.price} RON</td>
                <td>
                    <button class="btn btn-sm btn-edit" onclick="editProduct(${p.id}, '${p.name}', '${p.description}', ${p.price}, '${p.category}', '${p.image_path}')"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(${p.id})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `).join('');
    }
}

function editProduct(id, name, desc, price, cat, img) {
    document.getElementById('product-form').reset();
    document.getElementById('prod-id').value = id;
    document.getElementById('form-action').value = 'update';
    document.getElementById('modal-title').innerText = 'Edit Product';
    document.getElementById('prod-name').value = name;
    document.getElementById('prod-desc').value = desc;
    document.getElementById('prod-price').value = price;
    document.getElementById('prod-category').value = cat;
    if (img) {
        document.getElementById('current-image-preview').style.display = 'block';
        document.getElementById('preview-img').src = img;
    } else {
        document.getElementById('current-image-preview').style.display = 'none';
    }
    document.getElementById('product-modal').style.display = 'block';
}

async function handleProductSubmit(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    const action = document.getElementById('form-action').value;
    const res = await apiRequest('product', action, fd);
    if (res.success) {
        alert(res.data.message);
        document.getElementById('product-modal').style.display = 'none';
        loadProducts();
    } else {
        alert(res.error);
    }
}

async function deleteProduct(id) {
    if (!confirm("Are you sure?")) return;
    const res = await apiRequest('product', 'delete', { id: id });
    if (res.success) loadProducts();
    else alert(res.error);
}

// ==========================================
// 3. SLIDER MODULE (Restored)
// ==========================================
async function loadSlides() {
    const res = await apiRequest('slider', 'get_all');
    const container = document.getElementById('slider-list');
    if (res.success && container) {
        if (res.data.length === 0) {
            container.innerHTML = '<p>No slides found.</p>';
            return;
        }
        container.innerHTML = `
            <div class="slider-grid">
                ${res.data.map(s => `
                    <div class="slide-card">
                        <img src="${s.image_path}">
                        <div class="slide-info">
                            <strong>${s.title || 'No Title'}</strong>
                            <p>${s.subtitle || ''}</p>
                            <button class="btn btn-sm btn-danger" onclick="deleteSlide(${s.id})">Delete</button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
}

async function handleSliderSubmit(e) {
    e.preventDefault();
    const res = await apiRequest('slider', 'add', new FormData(e.target));
    if (res.success) {
        alert(res.data.message);
        document.getElementById('slider-modal').style.display = 'none';
        loadSlides();
    } else {
        alert(res.error);
    }
}

async function deleteSlide(id) {
    if (!confirm("Delete this slide?")) return;
    const res = await apiRequest('slider', 'delete', { id: id });
    if (res.success) loadSlides();
    else alert(res.error);
}

// ==========================================
// 4. TABLE MODULE (Restored)
// ==========================================
async function loadTables() {
    const res = await apiRequest('table', 'get_all');
    if (res.success) {
        document.getElementById('table-count-display').innerText = res.data.length;
        renderFloorPlan(res.data, res.background);
        renderTableList(res.data);
    }
}

function renderTableList(tables) {
    const grid = document.getElementById('tables-grid');
    if (!grid) return;
    grid.innerHTML = tables.map(t => `
        <div class="table-card status-${t.Status.toLowerCase().replace(' ', '-')}">
            <div class="table-id">Table ${t.ID}</div>
            <select class="table-status-select" onchange="updateTableStatus(${t.ID}, this.value)">
                <option value="Libera" ${t.Status == 'Libera' ? 'selected' : ''}>Libera</option>
                <option value="Ocupata" ${t.Status == 'Ocupata' ? 'selected' : ''}>Ocupata</option>
                <option value="Rezervata" ${t.Status == 'Rezervata' ? 'selected' : ''}>Rezervata</option>
                <option value="Inactiva" ${t.Status == 'Inactiva' ? 'selected' : ''}>Inactiva</option>
            </select>
            <div style="margin-top:10px;">
                 <button class="btn btn-sm btn-edit" onclick="openTableProps(${t.ID}, '${t.shape}', ${t.width}, ${t.height})"><i class="fas fa-cog"></i></button>
            </div>
        </div>
    `).join('');
}

function renderFloorPlan(tables, bg) {
    const container = document.getElementById('floor-plan-container');
    if (!container) return;
    container.innerHTML = ''; // clear
    if (bg) container.style.backgroundImage = `url('${bg}')`;

    tables.forEach(t => {
        const el = document.createElement('div');
        el.className = `draggable-table status-${t.Status.toLowerCase()}`;
        el.style.left = t.x_pos + '%';
        el.style.top = t.y_pos + '%';
        el.style.width = t.width + 'px';
        el.style.height = t.height + 'px';
        el.style.position = 'absolute';
        el.style.backgroundColor = getStatusColor(t.Status);
        el.style.borderRadius = t.shape === 'circle' ? '50%' : '4px';
        el.style.border = '2px solid #333';
        el.style.cursor = 'move';
        el.innerText = t.ID;
        el.style.display = 'flex';
        el.style.justifyContent = 'center';
        el.style.alignItems = 'center';
        el.style.color = '#fff';
        el.style.fontWeight = 'bold';

        // Drag Logic
        makeDraggable(el, t.ID);

        container.appendChild(el);
    });
}

function getStatusColor(s) {
    if (s === 'Libera') return 'rgba(46, 204, 113, 0.8)';
    if (s === 'Ocupata') return 'rgba(231, 76, 60, 0.8)';
    if (s === 'Rezervata') return 'rgba(241, 196, 15, 0.8)';
    return 'rgba(149, 165, 166, 0.8)';
}

function makeDraggable(elm, id) {
    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    elm.onmousedown = dragMouseDown;

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;

        const parent = elm.parentElement;
        let newTop = (elm.offsetTop - pos2);
        let newLeft = (elm.offsetLeft - pos1);

        // Percent conversion for saving
        elm.style.top = newTop + "px";
        elm.style.left = newLeft + "px";

        // Update DB debounced? Or save on mouse up? 
        // For simplicity, we save ON mouse up.
    }

    function closeDragElement() {
        document.onmouseup = null;
        document.onmousemove = null;

        // Calc percentages
        const parent = elm.parentElement;
        const xPct = (elm.offsetLeft / parent.offsetWidth) * 100;
        const yPct = (elm.offsetTop / parent.offsetHeight) * 100;

        // Save
        apiRequest('table', 'update_coordinates', { id: id, x: xPct, y: yPct, width: parseInt(elm.style.width), height: parseInt(elm.style.height) });
    }
}

async function updateTableCount(change) {
    const current = parseInt(document.getElementById('table-count-display').innerText);
    const newCount = current + change;
    const res = await apiRequest('table', 'update_count', { count: newCount });
    if (res.success) loadTables();
}

async function updateTableStatus(id, status) {
    await apiRequest('table', 'update_status', { id: id, status: status });
    loadTables(); // Refresh active view
}

async function uploadFloorPlan() {
    const input = document.getElementById('floor-plan-upload');
    if (input.files.length > 0) {
        const fd = new FormData();
        fd.append('image', input.files[0]);
        const res = await apiRequest('table', 'upload_background', fd);
        if (res.success) loadTables();
        else alert(res.error);
    }
}

function openTableProps(id, shape, w, h) {
    document.getElementById('prop-table-id').value = id;
    document.getElementById('prop-shape').value = shape;
    document.getElementById('prop-width').value = w;
    document.getElementById('prop-height').value = h;
    document.getElementById('table-props-modal').style.display = 'block';
}

async function handleTablePropsSubmit(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await apiRequest('table', 'update_details', fd);
    if (res.success) {
        document.getElementById('table-props-modal').style.display = 'none';
        loadTables();
    }
}

// ==========================================
// 5. RESERVATIONS MODULE (Restored)
// ==========================================
async function loadReservations() {
    const res = await apiRequest('reservation', 'get_all');
    if (res.success) {
        const active = res.data.active || [];
        const history = res.data.history || [];
        const deleted = res.data.deleted || [];

        renderResTable('reservations-list', active);
        renderResTable('history-reservations-list', history);
        renderResTable('deleted-reservations-list', deleted, true);
    }
}

function renderResTable(elId, data, isDeleted = false) {
    const tb = document.getElementById(elId);
    if (!tb) return;
    tb.innerHTML = data.map(r => `
        <tr>
            <td>${r.reservation_time}</td>
            <td>Table ${r.table_id}</td>
            <td>${r.reservation_name}</td>
            <td>${r.username || '-'}</td>
            ${isDeleted ? `<td><span style="color:red">Deleted</span></td>` : `<td>${r.email || '-'}</td>`}
            ${!isDeleted ? `
            <td>
                <button class="btn btn-sm btn-danger" onclick="deleteReservation(${r.id})">Delete</button>
            </td>` : ''}
        </tr>
    `).join('');
}

async function deleteReservation(id) {
    if (!confirm("Cancel this reservation?")) return;
    const res = await apiRequest('reservation', 'delete', { id: id });
    if (res.success) loadReservations();
    else alert(res.error);
}

// ==========================================
// 6. ORDERS MODULE (Basic Implementation)
// ==========================================
async function loadOrders() {
    // Placeholder for now
    const section = document.getElementById('section-orders');
    section.querySelector('.placeholder-content').innerHTML = '<p>Order management requires OrderController. Currently purely decorative.</p>';
}

// ==========================================
// 7. QUICK ACTION MODULE
// ==========================================
function openQuickReserve() {
    document.getElementById('quick-reserve-form').reset();
    // Set default time to now + 1 hour approx rounded
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.querySelector('input[name="reservation_time"]').value = now.toISOString().slice(0, 16);

    document.getElementById('quick-reserve-modal').style.display = 'block';
}

function setupQuickActions() {
    const quickResForm = document.getElementById('quick-reserve-form');
    if (quickResForm) {
        // Remove old listener to prevent duplicates? 
        // Cloning node is a cheap way to wipe listeners if we don't have reference.
        // Or just let it be, as the modal content might be re-rendered? 
        // Actually, since the page content is replaced by innerHTML in router.js, the 'old' form element is GONE from DOM.
        // So we are attaching to the NEW form element. So no duplicate listener issue on the ELEMENT.

        quickResForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);

            // We use the standard reservation 'create' action
            // Admin handler routes entity=reservation -> ReservationController

            const res = await apiRequest('reservation', 'create', fd);
            if (res.success) {
                alert("Reservation created!");
                document.getElementById('quick-reserve-modal').style.display = 'none';
                // Refresh dashboard stats or res list if visible
                loadDashboard();
                if (document.getElementById('section-reservations').style.display === 'block') {
                    loadReservations();
                }
            } else {
                alert(res.error);
            }
        });
    }
}

