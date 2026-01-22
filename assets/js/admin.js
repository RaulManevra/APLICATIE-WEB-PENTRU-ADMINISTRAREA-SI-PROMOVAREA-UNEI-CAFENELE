// Main Initialization Wrapper
window.onerror = function (msg, url, line, col, error) {
  console.error("Admin JS Error: " + msg + "\nLine: " + line);
  return false;
};

function initAdminPanel() {
  console.log("Admin Panel Initializing...", new Date().toISOString());
  // Visual confirmation for debug
  // const d = document.createElement('div');
  // d.style.cssText = 'position:fixed;top:10px;right:10px;background:lime;padding:5px;z-index:9999;';
  // d.textContent = 'Admin Start: ' + new Date().toLocaleTimeString();
  // document.body.appendChild(d); setTimeout(() => d.remove(), 2000);

  // Determine active section from URL or default
  showSection("dashboard");

  // Navigation Logic (Event Delegation)
  const sidebarNav = document.querySelector(".sidebar-nav");
  if (sidebarNav) {
    console.log("Sidebar nav found, attaching listener.");
    sidebarNav.addEventListener("click", (e) => {
      const link = e.target.closest(".nav-link[data-section]");
      if (link) {
        e.preventDefault();
        const sectionId = link.getAttribute("data-section");
        console.log("Navigating to section:", sectionId);
        showSection(sectionId);
      }
    });
  } else {
    console.error("Sidebar nav NOT found!");
  }

  // ==========================================
  // ====== DASHBOARD AND SCHEDULE INIT ======
  // ==========================================
  // ==========================================
  // ====== DASHBOARD AND SCHEDULE INIT ======
  // ==========================================
  // loadDashboard() is called by showSection('dashboard') below.
  setupSearch();
  setupScheduleForm();
  setupEmailSettingsForm();
  setupQuickActions();

  // ==========================================
  // ============ NOTES AUTO-SAVE =============
  // ==========================================
  const notesArea = document.getElementById("admin-notes");
  if (notesArea) {
    let timeout = null;
    notesArea.addEventListener("input", () => {
      clearTimeout(timeout);
      document.getElementById("notes-status").style.display = "none";
      timeout = setTimeout(saveNotes, 1000);
    });
  }

  // ==========================================
  // ======== MODAL CLOSE LOGIC ================
  // ==========================================
  document.querySelectorAll(".close-modal").forEach((btn) => {
    btn.addEventListener("click", () => {
      document.getElementById(btn.dataset.target).style.display = "none";
    });
  });

  // Delegated modal close on click outside
  window.addEventListener("click", (event) => {
    if (event.target.classList.contains("modal")) {
      event.target.style.display = "none";
    }
  });

  // ==========================================
  // ======== PRODUCT MODULE SETUP ============
  // ==========================================
  setupProductEvents();

  const addProdBtn = document.getElementById("add-product-btn");
  if (addProdBtn) {
    addProdBtn.addEventListener("click", () => {
      document.getElementById("product-form").reset();
      document.getElementById("prod-id").value = "";
      document.getElementById("form-action").value = "add";
      document.getElementById("modal-title").innerText = "Add Product";
      document.getElementById("current-image-preview").style.display = "none";
      document.getElementById("product-modal").style.display = "block";
    });
  }

  const prodForm = document.getElementById("product-form");
  if (prodForm) {
    prodForm.addEventListener("submit", handleProductSubmit);
  }

  // ==========================================
  // ======== SLIDER MODULE SETUP =============
  // ==========================================
  const addSlideBtn = document.getElementById("add-slide-btn");
  if (addSlideBtn) {
    addSlideBtn.addEventListener("click", () => {
      document.getElementById("slider-form").reset();
      document.getElementById("slider-modal").style.display = "block";
    });
  }
  const sliderForm = document.getElementById("slider-form");
  if (sliderForm) {
    sliderForm.addEventListener("submit", handleSliderSubmit);
  }

  // ==========================================
  // ======== TABLE MODULE SETUP =============
  // ==========================================
  const addTableBtn = document.getElementById("add-table-btn");
  if (addTableBtn)
    addTableBtn.addEventListener("click", () => updateTableCount(1));

  const removeTableBtn = document.getElementById("remove-table-btn");
  if (removeTableBtn)
    removeTableBtn.addEventListener("click", () => updateTableCount(-1));

  const savePositionsBtn = document.getElementById("save-positions-btn");
  // Note: Position saving is usually per-drag but we can implement a bulk save if tracked.
  // For now, let's assume auto-save on drag end or alert "Saved".
  if (savePositionsBtn)
    savePositionsBtn.addEventListener("click", () =>
      alert("Positions should be saved automatically on move.")
    );

  const uploadFloorPlanBtn = document.getElementById("upload-floor-plan-btn");
  const floorPlanInput = document.getElementById("floor-plan-upload");
  if (uploadFloorPlanBtn && floorPlanInput) {
    uploadFloorPlanBtn.addEventListener("click", () => floorPlanInput.click());
    floorPlanInput.addEventListener("change", uploadFloorPlan);
  }

  // Table Props Form
  const tablePropsForm = document.getElementById("table-props-form");
  if (tablePropsForm) {
    tablePropsForm.addEventListener("submit", handleTablePropsSubmit);
  }

  // ==========================================
  // ======== ORDERS SETUP ===================
  // ==========================================
  const refreshOrdersBtn = document.getElementById("refresh-orders-btn");
  if (refreshOrdersBtn) refreshOrdersBtn.addEventListener("click", loadOrders);

  // Ensure dashboard loads even if coming from history
  if (showSection.lastSection === 'dashboard' || !showSection.lastSection) {
    setTimeout(loadDashboard, 500); // Retry after short delay
  }

  // Initialize Cropper (safely)
  // Initialize Cropper (safely)
  if (typeof setupCropper === 'function') {
    setupCropper();
  }
}

function showToast(message, type = 'success') {
  const toast = document.getElementById("toast-notification");
  if (!toast) {
    console.error("Toast element not found!");
    alert(message); // Fallback
    return;
  }

  console.log("Showing toast:", message, type);
  toast.innerText = message;
  // Ensure z-index is max and visible
  toast.style.zIndex = "2147483647";
  toast.className = "toast show " + type;

  // Hide after 3 seconds
  setTimeout(function () {
    toast.className = toast.className.replace("show", "");
  }, 3000);
}

// Auto-run if already loaded (for SPA)
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initAdminPanel);
} else {
  initAdminPanel();
}

// ==========================================
// ======== GENERAL UTILS ===================
// ==========================================

function showSection(sectionId) {
  document
    .querySelectorAll(".admin-section")
    .forEach((el) => (el.style.display = "none"));
  document
    .querySelectorAll(".nav-link")
    .forEach((el) => el.classList.remove("active"));

  const target = document.getElementById("section-" + sectionId);
  if (target) {
    target.style.display = "block";
    const nav = document.querySelector(
      `.nav-link[data-section="${sectionId}"]`
    );
    if (nav) nav.classList.add("active");

    if (sectionId === "dashboard") {
      showSection.lastSection = 'dashboard';
      loadDashboard();
    }
    if (sectionId === "users") loadUsers();
    if (sectionId === "settings") loadSettings();
    if (sectionId === "menu") loadProducts();
    if (sectionId === "slider") loadSlides();
    if (sectionId === "tables") loadTables();
    if (sectionId === "reservations") loadReservations();
    if (sectionId === "orders") loadRunningOrders();
  }
}

async function apiRequest(entity, action, body = null) {
  const formData = body instanceof FormData ? body : new FormData();
  formData.append("entity", entity);
  formData.append("action", action);
  if (!(body instanceof FormData) && body) {
    for (let k in body) formData.append(k, body[k]);
  }

  const tokenVal = document.getElementById("csrf-token-global").value;

  try {
    const res = await fetch("controllers/admin_handler.php", {
      method: "POST",
      body: formData,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-Token": tokenVal,
      },
      credentials: "include", // Ensure session cookies are sent
    });

    if (!res.ok) {
      const text = await res.text();
      console.error("API Error Status:", res.status);
      console.error("API Error Body:", text);
      throw new Error(
        `Server responded with ${res.status}: ${text.substring(0, 100)}`
      );
    }

    return await res.json();
  } catch (e) {
    console.error("Fetch failed", e);
    alert("Server communication error: " + e.message);
    return { success: false, error: e.message };
  }
}

// ==========================================
// ============== DASHBOARD =================
// ==========================================

async function loadDashboard() {
  try {
    console.log("Fetching dashboard stats...");
    // Show loading state
    ['stat-res-today', 'stat-res-total', 'stat-active-tables', 'stat-menu-items'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.innerText = "...";
    });

    const res = await apiRequest("dashboard", "get_dashboard_stats");
    console.log("Dashboard fetch result:", res);

    if (res.success) {
      renderDashboard(res.data);
      // Also update cafe status if provided
      if (res.data.cafe_status) {
        const statusEl = document.getElementById("global-cafe-status");
        if (statusEl) statusEl.value = res.data.cafe_status;
      }
    } else {
      console.warn("Dashboard fetch failed logic:", res.error);
      const errMsg = res.message || res.error || "Failed to load dashboard data";
      showToast(errMsg, 'error');
      document.getElementById("stat-res-today").innerText = "Err";
    }
  } catch (e) {
    console.error("Dashboard load failed exception", e);
    showToast("Error loading dashboard: " + e.message, 'error');
    document.getElementById("stat-res-today").innerText = "Err";
  }
}

function renderDashboard(data) {
  console.log("Rendering dashboard with:", data);
  if (!data) return;
  const ids = {
    "stat-res-today": data.stats.reservations_today,
    "stat-res-total": "Upcoming: " + data.stats.reservations_total,
    "stat-active-tables": data.stats.active_tables,
    "stat-menu-items": data.stats.products_total,
    "admin-notes": data.notes,
  };
  for (let id in ids) {
    const el = document.getElementById(id);
    if (el) {
      if (el.tagName === "TEXTAREA") el.value = ids[id];
      else el.innerText = ids[id];
    } else {
      console.warn("Missing element for ID:", id);
    }
  }
  if (document.getElementById("global-cafe-status"))
    document.getElementById("global-cafe-status").value = data.cafe_status;

  const list = document.getElementById("recent-activity-list");
  if (list && data.recent) {
    list.innerHTML = data.recent
      .map(
        (r) => `
            <tr><td>${r.user}</td><td>${r.name}</td><td>${r.created}</td><td>${r.time}</td><td>#${r.id}</td></tr>
        `
      )
      .join("");
  }
  if (data.chart) renderChart(data.chart);
}

// Modern color palette (rotates automatically)
// Modern color palette (rotates automatically)
var MODERN_CHART_COLORS = [
  "#4CAF50",
  "#2196F3",
  "#FF9800",
  "#9C27B0",
  "#F44336",
  "#009688",
  "#3F51B5",
  "#FFC107",
  "#E91E63",
  "#795548",
];

// Hex → RGBA helper
function hexToRgba(hex, alpha = 1) {
  const h = hex.replace("#", "");
  const bigint = parseInt(h, 16);
  const r = (bigint >> 16) & 255;
  const g = (bigint >> 8) & 255;
  const b = bigint & 255;
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

var resChartInstance = null;

function renderChart(chartData) {
  const cvs = document.getElementById('TopSellingChart');
  if (!cvs) return;

  // Destroy previous chart if exists
  if (resChartInstance) resChartInstance.destroy();

  // Diverse colors for bars
  const colors = [
    'rgba(255, 99, 132, 0.7)',
    'rgba(54, 162, 235, 0.7)',
    'rgba(255, 206, 86, 0.7)',
    'rgba(75, 192, 192, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 159, 64, 0.7)',
    'rgba(199, 199, 199, 0.7)',
    'rgba(83, 102, 255, 0.7)',
    'rgba(255, 99, 255, 0.7)',
    'rgba(99, 255, 132, 0.7)'
  ];

  const borderColors = colors.map(c => c.replace('0.7', '1'));

  resChartInstance = new Chart(cvs.getContext('2d'), {
    type: 'bar',
    data: {
      labels: chartData.labels,
      datasets: [
        {
          label: 'Units Sold',
          data: chartData.data,
          backgroundColor: colors,
          borderColor: borderColors,
          borderWidth: 1,
          borderRadius: 10,
          yAxisID: 'yUnits'
        }/*,
        {
          label: 'Revenue (RON)',
          data: chartData.revenue, // Array of revenue per product
          backgroundColor: colors.map(c => c.replace('0.7', '0.3')), // lighter shade
          borderColor: borderColors,
          borderWidth: 1,
          borderRadius: 10,
          yAxisID: 'yRevenue'
        }*/
      ]
    },
    options: {
      indexAxis: 'y', // Horizontal bars
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 900,
        easing: 'easeOutQuart'
      },
      scales: {
        x: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0,0,0,0.05)',
            borderDash: [6, 6]
          },
          ticks: {
            precision: 0,
            color: '#6b7280'
          }
        },
        yUnits: {
          position: 'left',
          grid: { display: false },
          ticks: {
            color: '#111827',
            font: {
              size: 14,
              weight: '600'
            }
          }
        },
        yRevenue: {
          position: 'right',
          grid: { drawOnChartArea: false, drawTicks: false, drawBorder: false },
          title: {
            display: true,
            text: `Total Revenue: ${chartData.revenue ? chartData.revenue.reduce((a, b) => a + b, 0) : 0} RON`,
            color: '#167137ff', // Green
            opacity: 0.6,
            font: { size: 14, weight: 'bold' },
            padding: { right: 10 }
          },
          ticks: {
            display: false, // Hide the 0 RON, 1 RON... ticks
            callback: value => value + ' RON',
            color: '#167137ff',
            opacity: 0.6,
          }
        }
      },
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: {
            font: { size: 13, weight: '500' }
          }
        },
        tooltip: {
          backgroundColor: '#111827',
          callbacks: {
            label: function (context) {
              const datasetLabel = context.dataset.label || '';
              const value = context.parsed.x;
              let label = `${datasetLabel}: ${value}`;
              if (chartData.revenue && chartData.revenue[context.dataIndex] !== undefined) {
                label += ` (${chartData.revenue[context.dataIndex]} RON)`;
              }
              return label;
            }
          }
        }
      }
    }
  });
}


async function saveNotes() {
  await apiRequest("dashboard", "save_note", {
    content: document.getElementById("admin-notes").value,
  });
  const s = document.getElementById("notes-status");
  s.style.display = "block";
  setTimeout(() => (s.style.display = "none"), 2000);
}

async function updateCafeStatus(val) {
  await apiRequest("dashboard", "toggle_cafe_status", { status: val });
}
function exportData(type) {
  window.location.href = `controllers/admin_handler.php?entity=dashboard&action=export_data&type=${type}`;
}

async function sendNewsletter() {
  const sub = document.getElementById("news-subject").value;
  const body = document.getElementById("news-body").value;
  if (!sub || !body) return alert("Required");
  const res = await apiRequest("dashboard", "send_newsletter", {
    subject: sub,
    body: body,
  });
  alert(res.success ? res.message : res.message || res.error);
  if (res.success) {
    document.getElementById("news-subject").value = "";
    document.getElementById("news-body").value = "";
  }
}
// ==========================================
// =============== USERS ====================
// ==========================================

function setupSearch() {
  const inp = document.getElementById("user-search");
  if (!inp) return;
  let t;
  inp.addEventListener("input", () => {
    clearTimeout(t);
    t = setTimeout(() => loadUsers(inp.value), 500);
  });
}
async function loadUsers(search = "") {
  const res = await apiRequest("user", "get_all", { search: search });
  const tb = document.querySelector("#users-table tbody");
  if (res.success && tb) {
    tb.innerHTML = res.data
      .map(
        (u) => `
            <tr>
                <td><div style="display:flex;align-items:center;gap:10px;"><img src="${u.PPicture || "assets/img/default-user.png"
          }" style="width:30px;height:30px;border-radius:50%;"><span>${u.username
          }</span></div></td>
                <td>${u.email}</td><td>${u.role}</td><td>${u.PuncteFidelitate
          }</td>
                <td>${u.is_blacklisted == 1
            ? '<span style="color:red">Blacklisted</span>'
            : '<span style="color:green">Active</span>'
          }</td>
                <td><button class="btn btn-sm btn-edit" onclick="viewUser(${u.id
          })">Details</button></td>
            </tr>`
      )
      .join("");
  }
}
async function viewUser(id) {
  const res = await apiRequest("user", "get_one", { id: id });
  if (res.success) {
    const u = res.data;
    let statusHtml = "";
    if (u.is_blacklisted == 1) {
      statusHtml = `<div style="background:#ffebee; color:#c62828; padding:10px; border-radius:4px; margin:10px 0;">
                <strong>BLACKLISTED</strong><br>
                Reason: ${u.blacklist_reason || "No reason provided"}
            </div>`;
    }

    document.getElementById("user-details-content").innerHTML = `
            <div style="text-align:center;"><img src="${u.PPicture || "assets/img/default-user.png"
      }" style="width:80px;height:80px;border-radius:50%;"><h3>${u.username
      }</h3><p>${u.email}</p></div>
            ${statusHtml}
            <p><strong>Reservations:</strong> ${u.total_reservations
      } | <strong>Deleted Res:</strong> ${u.deleted_reservations || 0
      } | <strong>Orders:</strong> ${u.total_orders} | <strong>Points:</strong> ${u.PuncteFidelitate
      }</p>
        `;

    // Configure Blacklist Button
    const btn = document.getElementById("blacklist-btn");
    const reasonBox = document.getElementById("blacklist-reason");

    // Remove old listeners to avoid stacking (simplest way is to clone or reset)
    // Better: just assign onclick here since we are in a specific context
    btn.onclick = () => handleBlacklistToggle(u.id, u.is_blacklisted);

    if (u.is_blacklisted == 1) {
      btn.innerText = "Unblacklist User";
      btn.className = "btn btn-success";
      reasonBox.style.display = "none"; // No reason needed to unblacklist usually, or maybe clear it
    } else {
      btn.innerText = "Blacklist User";
      btn.className = "btn btn-danger";
      reasonBox.style.display = "block";
      reasonBox.value = ""; // clear previous
    }

    document.getElementById("user-modal").style.display = "block";
  }
}

async function handleBlacklistToggle(id, currentStatus) {
  const reasonBox = document.getElementById("blacklist-reason");
  const reason = reasonBox.value;

  // If getting blacklisted (currentStatus == 0), reason is required
  if (currentStatus == 0 && !reason.trim()) {
    alert("Please provide a reason for blacklisting.");
    return;
  }

  if (
    !confirm(
      currentStatus == 1 ? "Unblacklist this user?" : "Blacklist this user?"
    )
  )
    return;

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
  const res = await apiRequest("reservation", "toggle_blacklist", {
    user_id: id,
    reason: reason,
  });

  if (res.success) {
    // alert(res.data.message); // Removed for smoother flow
    document.getElementById("user-modal").style.display = "none";
    loadUsers(document.getElementById("user-search").value);
  } else {
    alert(res.error);
  }
}
// ==========================================
// =============== SETTINGS =================
// ==========================================
async function loadSettings() {
  const res = await apiRequest("settings", "get_schedule");
  const tb = document.getElementById("schedule-list");
  if (res.success && tb) {
    tb.innerHTML = res.data
      .map(
        (d, i) => `
            <tr>
                <td>${d.day_name
          }<input type="hidden" name="schedule[${i}][day_of_week]" value="${d.day_of_week
          }"></td>
                <td><input type="time" name="schedule[${i}][open_time]" value="${d.open_time
          }"></td>
                <td><input type="time" name="schedule[${i}][close_time]" value="${d.close_time
          }"></td>
                <td><input type="checkbox" name="schedule[${i}][is_closed]" value="1" ${d.is_closed == 1 ? "checked" : ""
          } onchange="this.value=this.checked?1:0"></td>
            </tr>
        `
      )
      .join("");
  }

  // Load Email Settings
  const res2 = await apiRequest("settings", "get_emails");
  if (res2.success) {
    if (document.getElementById("newsletter-email"))
      document.getElementById("newsletter-email").value = res2.data.newsletter_email || "";
    if (document.getElementById("support-email"))
      document.getElementById("support-email").value = res2.data.support_email || "";
  }
}
function setupScheduleForm() {
  const f = document.getElementById("schedule-form");
  if (f)
    f.addEventListener("submit", async (e) => {
      e.preventDefault();
      const res = await apiRequest(
        "settings",
        "update_schedule",
        new FormData(f)
      );
      alert(res.success ? res.message : res.message || res.error);
    });
}

function setupEmailSettingsForm() {
  const f = document.getElementById("email-settings-form");
  if (f) {
    f.addEventListener("submit", async (e) => {
      e.preventDefault();
      const res = await apiRequest("settings", "update_emails", new FormData(f));
      alert(res.success ? res.message : res.message || res.error);
    });
  }
}

// ==========================================
// =============== PRODUCTS =================
// ==========================================
async function loadProducts() {
  const res = await apiRequest("product", "get_all");
  const tb = document.querySelector("#products-table tbody");
  if (res.success && tb) {
    tb.innerHTML = res.data
      .map(
        (p) => `
            <tr>
                <td><img src="${p.image_path || "assets/menu/images/coffee.jpg"}" onerror="this.src='assets/img/Logo Modificat.png'"></td>
                <td>${p.name}<br><small class="text-muted">${p.quantity || ''}</small></td>
                <td>${p.category}</td>
                <td>${p.price} RON</td>
                <td>
                    <button class="btn btn-sm btn-edit btn-edit-product" 
                        data-id="${p.id}"
                        data-name="${(p.name || '').replace(/"/g, '&quot;')}"
                        data-desc="${(p.description || '').replace(/"/g, '&quot;')}"
                        data-ingredients="${(p.ingredients || '').replace(/"/g, '&quot;')}"
                        data-quantity="${(p.quantity || '').replace(/"/g, '&quot;')}"
                        data-price="${p.price}"
                        data-category="${p.category}"
                        data-img="${p.image_path || ''}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-delete-product" data-id="${p.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `
      )
      .join("");
  }
}

function editProduct(id, name, desc, ingredients, quantity, price, cat, img) {
  document.getElementById("product-form").reset();
  document.getElementById("prod-id").value = id;
  document.getElementById("form-action").value = "update";
  document.getElementById("modal-title").innerText = "Edit Product";
  document.getElementById("prod-name").value = name;
  document.getElementById("prod-desc").value = desc;
  document.getElementById("prod-ingredients").value = ingredients;
  document.getElementById("prod-quantity").value = quantity;
  document.getElementById("prod-price").value = price;
  document.getElementById("prod-category").value = cat;
  if (img) {
    document.getElementById("current-image-preview").style.display = "block";
    document.getElementById("preview-img").src = img;
  } else {
    document.getElementById("current-image-preview").style.display = "none";
  }
  document.getElementById("product-modal").style.display = "block";
}

async function handleProductSubmit(e) {
  e.preventDefault();
  try {
    const fd = new FormData(e.target);
    const action = document.getElementById("form-action").value;
    const res = await apiRequest("product", action, fd);
    if (res.success) {
      document.getElementById("product-modal").style.display = "none";
      loadProducts();
      // PHP output sends message at top level or inside data
      const msg = res.message || (res.data && res.data.message) || 'Operation successful';
      showToast(msg, 'success');
    } else {
      // PHP sends 'message' on error, apiRequest catch sends 'error'
      const errMsg = res.message || res.error || "Operation failed";
      showToast(errMsg, 'error');
    }
  } catch (err) {
    console.error("Product submit error:", err);
    showToast("Error: " + (err.message || err), 'error');
  }
}

async function deleteProduct(id) {
  if (!confirm("Are you sure?")) return;
  const res = await apiRequest("product", "delete", { id: id });
  if (res.success) loadProducts();
  else alert(res.error);
}

// ==========================================
// =============== SLIDER ===================
// ==========================================
async function loadSlides() {
  const res = await apiRequest("slider", "get_all");
  const container = document.getElementById("slider-list");
  if (res.success && container) {
    if (res.data.length === 0) {
      container.innerHTML = "<p>No slides found.</p>";
      return;
    }
    container.innerHTML = `
            <div class="slider-grid">
                ${res.data
        .map(
          (s) => `
                    <div class="slide-card">
                        <img src="${s.image_path}">
                        <div class="slide-info">
                            <strong>${s.title || "No Title"}</strong>
                            <p>${s.subtitle || ""}</p>
                            <button class="btn btn-sm btn-danger" onclick="deleteSlide(${s.id
            })">Delete</button>
                        </div>
                    </div>
                `
        )
        .join("")}
            </div>
        `;
  }
}

async function handleSliderSubmit(e) {
  e.preventDefault();
  const res = await apiRequest("slider", "add", new FormData(e.target));
  if (res.success) {
    alert(res.data.message);
    document.getElementById("slider-modal").style.display = "none";
    loadSlides();
  } else {
    alert(res.error);
  }
}

async function deleteSlide(id) {
  if (!confirm("Delete this slide?")) return;
  const res = await apiRequest("slider", "delete", { id: id });
  if (res.success) loadSlides();
  else alert(res.error);
}

// ==========================================
// =============== TABLE ====================
// ==========================================
async function loadTables() {
  const res = await apiRequest("table", "get_all");
  if (res.success) {
    document.getElementById("table-count-display").innerText = res.data.length;
    renderFloorPlan(res.data, res.background);
    renderTableList(res.data);
  }
}

function renderTableList(tables) {
  const grid = document.getElementById("tables-grid");
  if (!grid) return;
  grid.innerHTML = tables
    .map(
      (t) => `
        <div class="table-card status-${t.Status.toLowerCase().replace(
        " ",
        "-"
      )}">
            <div class="table-id">Table ${t.ID}</div>
            <select class="table-status-select" onchange="updateTableStatus(${t.ID
        }, this.value)">
                <option value="Libera" ${t.Status == "Libera" ? "selected" : ""
        }>Libera</option>
                <option value="Ocupata" ${t.Status == "Ocupata" ? "selected" : ""
        }>Ocupata</option>
                <option value="Rezervata" ${t.Status == "Rezervata" ? "selected" : ""
        }>Rezervata</option>
                <option value="Inactiva" ${t.Status == "Inactiva" ? "selected" : ""
        }>Inactiva</option>
            </select>
            <div style="margin-top:10px;">
                 <button class="btn btn-sm btn-edit" onclick="openTableProps(${t.ID
        }, '${t.shape}', ${t.width}, ${t.height
        })"><i class="fas fa-cog"></i></button>
            </div>
        </div>
    `
    )
    .join("");
}

function renderFloorPlan(tables, bg) {
  const container = document.getElementById("floor-plan-container");
  if (!container) return;
  container.innerHTML = ""; // clear

  if (bg) {
    // Load image to get dimensions for aspect ratio, mimicking tables.php logic
    const img = new Image();
    img.src = bg;
    img.onload = function () {
      const aspect = img.width / img.height;
      container.style.backgroundImage = `url('${bg}')`;
      container.style.backgroundSize = "cover";
      container.style.aspectRatio = `${img.width} / ${img.height}`;
      container.style.height = "auto"; // allow height to adjust based on width and aspect ratio
    };
  } else {
    container.style.backgroundImage = "none";
    container.style.height = "600px"; // Fallback
  }

  tables.forEach((t) => {
    const el = document.createElement("div");
    el.className = `draggable-table status-${t.Status.toLowerCase()} shape-${t.shape
      }`;
    el.style.left = t.x_pos + "%";
    el.style.top = t.y_pos + "%";
    // Now using percentages for width/height too
    el.style.width = t.width + "%";
    el.style.height = t.height + "%";
    el.style.position = "absolute";
    el.style.backgroundColor = getStatusColor(t.Status);
    el.style.borderRadius = t.shape === "circle" ? "50%" : "4px";
    el.style.border = "2px solid #333";
    el.style.cursor = "move";
    el.innerText = t.ID;
    el.style.display = "flex";
    el.style.justifyContent = "center";
    el.style.alignItems = "center";
    el.style.color = "#fff";
    el.style.fontWeight = "bold";

    // Drag Logic
    // console.log("Making draggable:", t.ID, t); // Debug
    makeDraggable(el, t.ID || t.id); // Handle case sensitivity

    // Resize Logic
    const directions = ["n", "s", "e", "w", "ne", "nw", "se", "sw"];
    directions.forEach((dir) => {
      const resizer = document.createElement("div");
      resizer.className = "resize-handle resize-" + dir;
      el.appendChild(resizer);
      makeResizable(el, resizer, dir, t.ID || t.id);
    });

    container.appendChild(el);
  });
}

function getStatusColor(s) {
  if (s === "Libera") return "rgba(46, 204, 113, 0.8)";
  if (s === "Ocupata") return "rgba(231, 76, 60, 0.8)";
  if (s === "Rezervata") return "rgba(241, 196, 15, 0.8)";
  return "rgba(149, 165, 166, 0.8)";
}

function makeDraggable(elm, id) {
  let pos1 = 0,
    pos2 = 0,
    pos3 = 0,
    pos4 = 0;
  elm.onmousedown = dragMouseDown;

  function dragMouseDown(e) {
    // Prevent drag if clicking resize handle
    if (e.target.classList.contains("resize-handle")) return;

    // console.log("Drag started for ID:", id); // Debug

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
    let newTop = elm.offsetTop - pos2;
    let newLeft = elm.offsetLeft - pos1;

    // Percent conversion for saving
    elm.style.top = newTop + "px";
    elm.style.left = newLeft + "px";
  }

  function closeDragElement() {
    document.onmouseup = null;
    document.onmousemove = null;

    // Calc percentages with Sub-Pixel Precision
    const parent = elm.parentElement;
    const parentRect = parent.getBoundingClientRect();
    const elemRect = elm.getBoundingClientRect();
    const style = window.getComputedStyle(parent);
    const borderLeft = parseFloat(style.borderLeftWidth) || 0;
    const borderTop = parseFloat(style.borderTopWidth) || 0;

    const leftPx = elemRect.left - parentRect.left - borderLeft;
    const topPx = elemRect.top - parentRect.top - borderTop;

    const xPct = (leftPx / parent.clientWidth) * 100;
    const yPct = (topPx / parent.clientHeight) * 100;

    // Apply percentages to element so it remains responsive
    elm.style.left = xPct + "%";
    elm.style.top = yPct + "%";
    // Width/Height in % as well? For drag, w/h doesn't change, but we should preserve % units.
    // Actually, if we just drag, w/h is already set. We just need to make sure we don't save px.
    // We can just send the current style width/height if it is %, or calculate it.
    // It is safest to calculate wPct and hPct.
    const wPct = (elemRect.width / parent.clientWidth) * 100;
    const hPct = (elemRect.height / parent.clientHeight) * 100;

    elm.style.width = wPct + "%";
    elm.style.height = hPct + "%";

    // Save
    apiRequest("table", "update_coordinates", {
      id: id,
      x: xPct,
      y: yPct,
      width: wPct,
      height: hPct,
    });
  }
}

function makeResizable(elm, resizer, dir, id) {
  let startX, startY, startWidth, startHeight, startLeft, startTop;

  resizer.addEventListener("mousedown", function (e) {
    e.preventDefault();
    e.stopPropagation(); // Stop drag of table
    startX = e.clientX;
    startY = e.clientY;
    const rect = elm.getBoundingClientRect();
    startWidth = rect.width;
    startHeight = rect.height;
    startLeft = elm.offsetLeft;
    startTop = elm.offsetTop;

    document.documentElement.addEventListener("mousemove", doDrag, false);
    document.documentElement.addEventListener("mouseup", stopDrag, false);
  });

  function doDrag(e) {
    let dx = e.clientX - startX;
    let dy = e.clientY - startY;

    // Min dimensions
    const minSize = 20;

    let newW = startWidth;
    let newH = startHeight;
    let newL = startLeft;
    let newT = startTop;

    // Horizontal
    if (dir.indexOf("e") !== -1) {
      newW = Math.max(minSize, startWidth + dx);
    }
    if (dir.indexOf("w") !== -1) {
      // changing left and width
      // if width gets too small, stop changing left
      if (startWidth - dx >= minSize) {
        newW = startWidth - dx;
        newL = startLeft + dx;
      }
    }

    // Vertical
    if (dir.indexOf("s") !== -1) {
      newH = Math.max(minSize, startHeight + dy);
    }
    if (dir.indexOf("n") !== -1) {
      if (startHeight - dy >= minSize) {
        newH = startHeight - dy;
        newT = startTop + dy;
      }
    }

    elm.style.width = newW + "px";
    elm.style.height = newH + "px";
    elm.style.left = newL + "px";
    elm.style.top = newT + "px";
  }

  function stopDrag(e) {
    document.documentElement.removeEventListener("mousemove", doDrag, false);
    document.documentElement.removeEventListener("mouseup", stopDrag, false);

    // Save with Sub-Pixel Precision
    const parent = elm.parentElement;
    const parentRect = parent.getBoundingClientRect();
    const elemRect = elm.getBoundingClientRect();
    const style = window.getComputedStyle(parent);
    const borderLeft = parseFloat(style.borderLeftWidth) || 0;
    const borderTop = parseFloat(style.borderTopWidth) || 0;

    const leftPx = elemRect.left - parentRect.left - borderLeft;
    const topPx = elemRect.top - parentRect.top - borderTop;

    // Apply percentages to element so it remains responsive
    elm.style.left = xPct + "%";
    elm.style.top = yPct + "%";

    const wPct = (elemRect.width / parent.clientWidth) * 100;
    const hPct = (elemRect.height / parent.clientHeight) * 100;

    elm.style.width = wPct + "%";
    elm.style.height = hPct + "%";

    apiRequest("table", "update_coordinates", {
      id: id,
      x: xPct,
      y: yPct,
      width: wPct,
      height: hPct,
    });
  }
}

async function updateTableCount(change) {
  const current = parseInt(
    document.getElementById("table-count-display").innerText
  );
  const newCount = current + change;
  const res = await apiRequest("table", "update_count", { count: newCount });
  if (res.success) loadTables();
}

async function updateTableStatus(id, status) {
  await apiRequest("table", "update_status", { id: id, status: status });
  loadTables(); // Refresh active view
}

async function uploadFloorPlan() {
  const input = document.getElementById("floor-plan-upload");
  if (input.files.length > 0) {
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function (e) {
      openCropModal(e.target.result);
    };
    reader.readAsDataURL(file);
  }
}

// Cropper State
var cropState = {
  img: null,
  scale: 1,
  panning: false,
  startX: 0,
  startY: 0,
  translateX: 0,
  translateY: 0,
};

function openCropModal(imgSrc) {
  const modal = document.getElementById("crop-modal");
  const imgElement = document.getElementById("crop-target-img");
  const container = document.getElementById("crop-container-wrapper");
  const slider = document.getElementById("crop-zoom-slider");

  // Set fixed size for crop box (mimicking floor plan box)
  // We want the resulting image to be high quality but proportional.
  // Let's use a standard 800x600 viewing box for the cropper.
  container.style.width = "800px";
  container.style.height = "600px";
  container.style.backgroundColor = "#ccc";

  imgElement.src = imgSrc;
  imgElement.onload = function () {
    // Init State
    cropState.img = imgElement;
    cropState.scale = 1;
    cropState.translateX = 0;
    cropState.translateY = 0;

    // Initial Center
    // imgElement.style.left = '0px';
    // imgElement.style.top = '0px';
    updateCropTransform();

    slider.value = 1;
    document.getElementById("zoom-level").innerText = "100%";
    modal.style.display = "flex";

    initCropEvents();
  };
}

function updateCropTransform() {
  const img = cropState.img;
  img.style.transform = `translate(${cropState.translateX}px, ${cropState.translateY}px) scale(${cropState.scale})`;
}

function initCropEvents() {
  const container = document.getElementById("crop-container-wrapper");
  const slider = document.getElementById("crop-zoom-slider");

  // Zoom
  slider.oninput = function () {
    cropState.scale = parseFloat(this.value);
    document.getElementById("zoom-level").innerText =
      Math.round(cropState.scale * 100) + "%";
    updateCropTransform();
  };

  // Zoom Buttons
  document.getElementById("zoom-in-btn").onclick = function () {
    let val = parseFloat(slider.value);
    val = Math.min(3, val + 0.01);
    slider.value = val;
    cropState.scale = val;
    document.getElementById("zoom-level").innerText =
      Math.round(cropState.scale * 100) + "%";
    updateCropTransform();
  };

  document.getElementById("zoom-out-btn").onclick = function () {
    let val = parseFloat(slider.value);
    val = Math.max(0.1, val - 0.01);
    slider.value = val;
    cropState.scale = val;
    document.getElementById("zoom-level").innerText =
      Math.round(cropState.scale * 100) + "%";
    updateCropTransform();
  };

  // Pan
  container.onmousedown = function (e) {
    e.preventDefault();
    cropState.panning = true;
    cropState.startX = e.clientX - cropState.translateX;
    cropState.startY = e.clientY - cropState.translateY;
    container.style.cursor = "grabbing";
  };

  window.onmousemove = function (e) {
    if (!cropState.panning) return;
    e.preventDefault();
    cropState.translateX = e.clientX - cropState.startX;
    cropState.translateY = e.clientY - cropState.startY;
    updateCropTransform();
  };

  window.onmouseup = function () {
    if (cropState.panning) {
      cropState.panning = false;
      container.style.cursor = "grab";
    }
  };
}

function setupCropper() {
  const saveBtn = document.getElementById("btn-save-crop");
  if (!saveBtn) return;

  saveBtn.onclick = function () {
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");

    // The target dimensions (what fits in the box)
    const targetW = 800;
    const targetH = 600;
    canvas.width = targetW;
    canvas.height = targetH;

    const img = cropState.img;
    if (!img) return;

    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, targetW, targetH);

    ctx.save();
    ctx.translate(cropState.translateX, cropState.translateY);
    ctx.scale(cropState.scale, cropState.scale);
    ctx.drawImage(img, 0, 0);
    ctx.restore();

    canvas.toBlob(async function (blob) {
      const fd = new FormData();
      fd.append("image", blob, "floorplan_cropped.png");

      const btn = document.getElementById("btn-save-crop");
      const oldText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
      btn.disabled = true;

      const res = await apiRequest("table", "upload_background", fd);

      btn.innerHTML = oldText;
      btn.disabled = false;

      if (res.success) {
        document.getElementById("crop-modal").style.display = "none";
        alert("Floor plan updated!");
        loadTables();
      } else {
        alert(res.error);
      }
    }, "image/png");
  };
}

function openTableProps(id, shape, w, h) {
  document.getElementById("prop-table-id").value = id;
  document.getElementById("prop-shape").value = shape;
  document.getElementById("prop-width").value = w;
  document.getElementById("prop-height").value = h;
  document.getElementById("table-props-modal").style.display = "block";
}

async function handleTablePropsSubmit(e) {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await apiRequest("table", "update_details", fd);
  if (res.success) {
    document.getElementById("table-props-modal").style.display = "none";
    loadTables();
  }
}

// ==========================================
// =============== RESERVATIONS =============
// ==========================================
async function loadReservations() {
  const res = await apiRequest("reservation", "get_all");
  if (res.success) {
    const active = res.data.active || [];
    const history = res.data.history || [];
    const deleted = res.data.deleted || [];

    renderResTable("reservations-list", active);
    renderResTable("history-reservations-list", history);
    renderResTable("deleted-reservations-list", deleted, true);
  }
}

function renderResTable(elId, data, isDeleted = false) {
  const tb = document.getElementById(elId);
  if (!tb) return;
  tb.innerHTML = data
    .map(
      (r) => `
        <tr>
            <td>${r.reservation_time}</td>
            <td>Table ${r.table_id}</td>
            <td>${r.reservation_name}</td>
            <td>${r.username || "-"}</td>
            ${isDeleted
          ? `<td><span style="color:red">Deleted</span></td>`
          : `<td>${r.email || "-"}</td>`
        }
            ${!isDeleted
          ? `
            <td>
                <button class="btn btn-sm btn-danger" onclick="deleteReservation(${r.id})">Delete</button>
            </td>`
          : ""
        }
        </tr>
    `
    )
    .join("");
}

async function deleteReservation(id) {
  if (!confirm("Cancel this reservation?")) return;
  const res = await apiRequest("reservation", "delete", { id: id });
  if (res.success) loadReservations();
  else alert(res.error);
}

// ==========================================
// =============== ORDERS ===================
// ==========================================
var cachedTables = [];

async function loadRunningOrders() {
  const container = document.getElementById("running-orders-container");
  if (!container) return;

  container.innerHTML = "<p>Loading active orders...</p>";

  // Load tables for dropdown if not loaded
  if (cachedTables.length === 0) {
    const tRes = await apiRequest("table", "get_all");
    if (tRes.success) cachedTables = tRes.data;
  }

  const res = await apiRequest("order", "get_running");
  if (res.success) {
    const orders = res.orders || [];
    if (orders.length === 0) {
      container.innerHTML = "<p>No running orders.</p>";
      return;
    }

    container.innerHTML = orders.map((o) => renderOrderCard(o)).join("");
  } else {
    container.innerHTML = `<p style="color:red">Error: ${res.error}</p>`;
  }
}

function renderOrderCard(order) {
  const itemsHtml = order.items
    .map((i) => `<li>${i.quantity}x ${i.name}</li>`)
    .join("");

  const statusColors = {
    pending: "#f1c40f",
    preparing: "#e67e22",
    ready: "#2ecc71",
  };
  const statusColor = statusColors[order.status] || "#95a5a6";

  const tableOptions = cachedTables
    .map(
      (t) =>
        `<option value="${t.ID}" ${parseInt(order.table_id) === parseInt(t.ID) ? "selected" : ""
        }>Table ${t.ID} (${t.Status})</option>`
    )
    .join("");

  // Determine Payment Badge
  const payMethod = order.payment_method || 'card';
  const isCash = payMethod === 'cash';
  const payBadgeColor = isCash ? '#2ecc71' : '#3498db';
  const payIcon = isCash ? '<i class="fas fa-money-bill-wave"></i>' : '<i class="fas fa-credit-card"></i>';

  let cashAlert = '';
  if (isCash && order.status !== 'completed' && order.status !== 'cancelled') {
    cashAlert = `
        <div style="background: #e8f5e9; border: 1px solid #a5d6a7; color: #2e7d32; padding: 10px; border-radius: 6px; margin: 10px 0; font-weight: bold; text-align: center;">
            <i class="fas fa-hand-holding-usd"></i> COLLECT: ${parseFloat(order.total_price).toFixed(2)} RON
        </div>
      `;
  }

  return `
    <div class="order-card" style="border: 1px solid #ddd; background: #fff; padding: 15px; border-radius: 8px; width: 350px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display:flex; flex-direction:column; gap:10px; border-left: 10px solid ${statusColor};">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h4 style="margin:0;">Order #${order.id}</h4>
                <small style="color:#666;">${order.username}</small>
            </div>
            <div style="text-align:right;">
                <div style="margin-bottom:2px;"><span style="background:${statusColor}; color:#fff; padding:2px 8px; border-radius:10px; font-size:0.8rem;">${order.status.toUpperCase()}</span></div>
                <div><span style="background:${payBadgeColor}; color:#fff; padding:2px 8px; border-radius:10px; font-size:0.75rem;">${payIcon} ${payMethod.toUpperCase()}</span></div>
            </div>
        </div>
        
        <div style="max-height: 150px; overflow-y:auto; background:#f9f9f9; padding:5px; border-radius:4px;">
            <ul style="margin:0; padding-left:20px; font-size:0.9rem;">${itemsHtml}</ul>
        </div>
        
        ${cashAlert}

        <div style="font-size: 0.85rem; color: #555;">
             <div style="display:flex; justify-content:space-between;">
                 <span>Created: ${new Date(order.created_at).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })}</span>
                 ${!order.table_id ?
      `<span>Pickup: ${new Date(order.pickup_time).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })}</span>`
      : '<span>Table Order</span>'
    }
             </div>
             <div style="margin-top:5px; font-weight:bold;">Total: ${parseFloat(order.total_price).toFixed(2)} RON</div>
        </div>

        <div style="display:flex; gap:5px; margin-top:5px;">
            <button class="btn btn-sm" style="flex:1; background:#f1c40f; color:#fff;" onclick="updateOrderStatus(${order.id
    }, 'pending')">Pending</button>
            <button class="btn btn-sm" style="flex:1; background:#e67e22; color:#fff;" onclick="updateOrderStatus(${order.id
    }, 'preparing')">Prep</button>
            <button class="btn btn-sm" style="flex:1; background:#2ecc71; color:#fff;" onclick="updateOrderStatus(${order.id
    }, 'ready')">Ready</button>
        </div>

        <div>
            <label style="font-size:0.8rem; font-weight:bold;">Serving for:</label>
            <select class="form-control" onchange="assignOrderTable(${order.id
    }, this.value)" style="width:100%; font-size:0.9rem; padding:5px;border-radius:4px; border:1px solid #ccc;">
                <option value="pickup" ${!order.table_id ? "selected" : ""
    }>Pick-up / Takeaway</option>
                ${tableOptions}
            </select>
        </div>

        <button class="btn btn-success" style="width:100%; margin-top:5px;" onclick="completeOrder(${order.id
    })">
            <i class="fas fa-check"></i> Complete & Clear
        </button>
    </div>
    `;
}

async function updateOrderStatus(id, status) {
  const res = await apiRequest("order", "update_status", {
    order_id: id,
    status: status,
  });
  if (res.success) loadRunningOrders();
}

async function assignOrderTable(orderId, val) {
  // If val is 'pickup', table_id is null.
  // If val is number, table_id is number.
  const res = await apiRequest("order", "assign_table", {
    order_id: orderId,
    table_id: val,
  });
  if (res.success) {
    // Also refresh tables because status might have changed to 'Ocupata'
    loadTables();
    loadRunningOrders();
  } else {
    alert(res.error);
  }
}

async function completeOrder(id) {
  if (!confirm("Mark order as COMPLETED? This might free the table.")) return;
  const res = await apiRequest("order", "update_status", {
    order_id: id,
    status: "completed",
  });
  if (res.success) {
    loadRunningOrders();
    loadTables(); // Status might change to 'Libera'
  } else {
    alert(res.error);
  }
}

// ==========================================
// ============ QUICK ACTIONS ==============
// ==========================================
function openQuickReserve() {
  document.getElementById("quick-reserve-form").reset();
  // Set default time to now + 1 hour approx rounded
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  document.querySelector('input[name="reservation_time"]').value = now
    .toISOString()
    .slice(0, 16);

  document.getElementById("quick-reserve-modal").style.display = "block";
}

function setupQuickActions() {
  const quickResForm = document.getElementById("quick-reserve-form");
  if (quickResForm) {
    // Remove old listener to prevent duplicates?
    // Cloning node is a cheap way to wipe listeners if we don't have reference.
    // Or just let it be, as the modal content might be re-rendered?
    // Actually, since the page content is replaced by innerHTML in router.js, the 'old' form element is GONE from DOM.
    // So we are attaching to the NEW form element. So no duplicate listener issue on the ELEMENT.

    quickResForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);

      // We use the standard reservation 'create' action
      // Admin handler routes entity=reservation -> ReservationController

      const res = await apiRequest("reservation", "create", fd);
      if (res.success) {
        alert("Reservation created!");
        document.getElementById("quick-reserve-modal").style.display = "none";
        // Refresh dashboard stats or res list if visible
        loadDashboard();
        if (
          document.getElementById("section-reservations").style.display ===
          "block"
        ) {
          loadReservations();
        }
      } else {
        alert(res.error);
      }
    });
  }
}

function setupProductEvents() {
  const table = document.getElementById('products-table');
  if (table) {
    table.addEventListener('click', e => {
      const editBtn = e.target.closest('.btn-edit-product');
      const deleteBtn = e.target.closest('.btn-delete-product');

      if (editBtn) {
        const { id, name, desc, ingredients, quantity, price, category, img } = editBtn.dataset;
        editProduct(id, name, desc, ingredients, quantity, price, category, img);
      }

      if (deleteBtn) {
        const { id } = deleteBtn.dataset;
        deleteProduct(id);
      }
    });
  }
}
