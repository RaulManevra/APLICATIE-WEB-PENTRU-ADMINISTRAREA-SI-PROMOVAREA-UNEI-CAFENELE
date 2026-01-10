/**
 * Mazi Coffee - Main Entry Point
 */
import { safeFetch } from './modules/api.js';
import { loadPage } from './modules/router.js';
import { refreshCurrentUser } from './modules/auth.js';
import { initProfilePopup, closeProfilePopup } from './modules/profile.js';
import { showModal } from './modules/utils.js';
import { updateHeaderUI } from './modules/ui.js';
// ====================================
// ======== GLOBAL TOKEN CHECK ========
// ====================================
(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    if (token) {
        console.log("Token detected and saved:", token);
        sessionStorage.setItem('orderToken', token);
        // Clean URL to hide token, keeping other params
        urlParams.delete('token');
        const newQuery = urlParams.toString();
        const newUrl = window.location.pathname + (newQuery ? '?' + newQuery : '');
        window.history.replaceState({}, document.title, newUrl);
    }
})();

// ====================================
// ======== EVENT LISTENERS ===========
// ====================================

// Form Submission (Add to Cart)
document.addEventListener("click", async e => {
    const btn = e.target.closest('.add-to-cart-btn, .add-to-cart-btn-full');
    if (btn) {
        e.preventDefault();
        e.stopPropagation();

        const productId = btn.dataset.id;
        if (!productId) return;

        // Visual feedback
        const icon = btn.querySelector('i');
        const originalClass = icon.className;
        icon.className = "fa-solid fa-spinner fa-spin"; // Loading state

        try {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            formData.append('quantity', 1);

            const res = await safeFetch('?page=cart_handler&action=add', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.success) {
                // Success feedback
                icon.className = "fa-solid fa-check";
                btn.classList.add('success'); // Add success class for CSS animation

                // Add simple toast notification
                const toast = document.createElement('div');
                toast.className = 'cart-toast';
                toast.innerHTML = '<i class="fa-solid fa-check-circle"></i> Added to cart!';
                document.body.appendChild(toast);

                // Trigger reflow
                toast.offsetHeight;
                toast.classList.add('show');

                setTimeout(() => {
                    icon.className = originalClass;
                    btn.classList.remove('success');

                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, 1500);

                // Optional: Update badge if we had one
                // updateCartBadge(data.data.total_items);
            } else {
                showModal(data.message || "Failed to add to cart.");
                icon.className = originalClass;
            }
        } catch (err) {
            console.error(err);
            showModal("An error occurred. Please try again.");
            icon.className = originalClass;
        }
    }
});
// ====================================
// Form Submission (Login/Register)
document.addEventListener("submit", async e => {
    const form = e.target;
    if (form.matches("form[action*='controllers/register_handler.php'], form[action*='controllers/login_handler.php'], form[action*='controllers/profile_handler.php'], form[action*='forgot_password_handler']")) {
        e.preventDefault();
        const formData = new FormData(form);
        const action = form.getAttribute("action");

        try {
            const res = await safeFetch(action, { method: "POST", body: formData });
            const data = await res.json();

            if (data.success) {
                // Determine next steps
                const promises = [refreshCurrentUser()];

                if (data.redirect) {
                    promises.push(loadPage(data.redirect));
                }

                if (action.includes('profile_handler.php')) {
                    await Promise.all(promises);
                    // Special handling for profile picture upload
                    // Close modal
                    const modal = document.getElementById("modal");
                    if (modal) modal.style.display = "none";

                    // Force image refresh by appending timestamp
                    const timestamp = new Date().getTime();
                    document.querySelectorAll('img[src*="assets/uploads/profile_pictures/"], img[id="profile-pic-trigger"]').forEach(img => {
                        const src = img.getAttribute('src').split('?')[0];
                        img.setAttribute('src', `${src}?t=${timestamp}`);
                    });
                } else {
                    await Promise.all(promises);
                }
            } else {
                showModal(data.message);
            }
        } catch (err) {
            console.error(err);
        }
    }
});
// ====================================
// Navigation Click Handler (delegation for .nav-link and .popup-action)
document.addEventListener("click", async e => {
    const target = e.target.closest('.nav-link, .popup-action');
    if (!target) return;

    const page = target.dataset.page;
    if (!page) return;

    // Special handling for Logout
    if (page === "logout") {
        e.preventDefault();
        try {
            const res = await safeFetch('controllers/logout.php', { method: 'GET' });
            const data = await res.json();
            if (data.success) {
                await Promise.all([
                    refreshCurrentUser(),
                    loadPage(data.redirect)
                ]);
                closeProfilePopup();
            } else {
                showModal("Logout failed.");
            }
        } catch (err) {
            console.error(err);
        }
        return;
    }

    // Standard Navigation
    if (target.classList.contains('nav-link') || target.classList.contains('popup-action')) {
        e.preventDefault();
        await loadPage(page);

        if (target.classList.contains('popup-action')) {
            closeProfilePopup();
        }
    }
});
// ====================================
// History State Manager
window.addEventListener("popstate", e => {
    if (e.state && e.state.page) {
        loadPage(e.state.page, false);
    }
});

// Initial Load
const urlParams = new URLSearchParams(window.location.search);
loadPage(urlParams.get("page") || "home", false);

// Initialize Header UI immediately with data from PHP
updateHeaderUI();

// Initialize Popup logic
document.addEventListener('DOMContentLoaded', initProfilePopup);
// Fallback if script loads after DOMContentLoaded
if (document.readyState === 'interactive' || document.readyState === 'complete') {
    initProfilePopup();
}

window.addEventListener('DOMContentLoaded', () => window.scrollTo(0, 0));
window.addEventListener('load', () => window.scrollTo(0, 0));
window.addEventListener('popstate', () => window.scrollTo(0, 0));

document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    const href = a.getAttribute('href') || '';
    if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
    // for normal navigation or SPA routers, ensure page is at top
    setTimeout(() => window.scrollTo(0, 0), 50);
});

// ====================================
// Menu Details Modal Logic
// ====================================
function openProductDetailsModal(name, desc, price, img, ingredients) {
    const modal = document.getElementById('product-details-modal');
    if (!modal) return;

    document.getElementById('modal-prod-name').innerText = name || '';
    document.getElementById('modal-prod-desc').innerText = desc || '';
    document.getElementById('modal-prod-price').innerText = (price || '0') + ' RON';
    const imgEl = document.getElementById('modal-prod-img');
    if (imgEl) imgEl.src = img || 'assets/menu/images/default_coffee.jpg';

    const ingEl = document.getElementById('modal-prod-ingredients');
    const ingSec = document.getElementById('modal-ingredients-section');

    if (ingEl && ingSec) {
        if (ingredients && ingredients.trim() !== '') {
            ingEl.innerText = ingredients;
            ingSec.style.display = 'block';
        } else {
            ingSec.style.display = 'none';
        }
    }

    modal.style.display = 'block';
}

function closeProductDetailsModal() {
    const modal = document.getElementById('product-details-modal');
    if (modal) modal.style.display = 'none';
}

// Global Click Delegation for Menu
document.addEventListener('click', e => {
    // 1. Product Card Click (Open Modal)
    const card = e.target.closest('.product-card');
    if (card) {
        // Prevent opening if clicking add-to-cart
        if (e.target.closest('.add-to-cart-btn-full')) return;

        const { name, desc, price, img, ingredients } = card.dataset;
        openProductDetailsModal(name, desc, price, img, ingredients);
    }

    // 2. Close Modal (Button)
    if (e.target.closest('.close-modal')) {
        closeProductDetailsModal();
    }

    // 3. Close Modal (Click Outside)
    const modal = document.getElementById('product-details-modal');
    if (modal && e.target === modal) {
        closeProductDetailsModal();
    }
});



