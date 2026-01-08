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
// ======== EVENT LISTENERS ===========
// ====================================

// Form Submission (Add to Cart)
document.addEventListener("click", async e => {
    const btn = e.target.closest('.add-to-cart-btn');
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
                setTimeout(() => {
                    icon.className = originalClass;
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
    if (form.matches("form[action*='controllers/register_handler.php'], form[action*='controllers/login_handler.php'], form[action*='controllers/profile_handler.php']")) {
        e.preventDefault();
        const formData = new FormData(form);
        const action = form.getAttribute("action");

        try {
            const res = await safeFetch(action, { method: "POST", body: formData });
            const data = await res.json();

            if (data.success) {
                await refreshCurrentUser();

                // Special handling for profile picture upload
                if (action.includes('profile_handler.php')) {
                    // Close modal
                    const modal = document.getElementById("modal");
                    if (modal) modal.style.display = "none";

                    // Force image refresh by appending timestamp
                    const timestamp = new Date().getTime();
                    document.querySelectorAll('img[src*="assets/uploads/profile_pictures/"], img[id="profile-pic-trigger"]').forEach(img => {
                        const src = img.getAttribute('src').split('?')[0];
                        img.setAttribute('src', `${src}?t=${timestamp}`);
                    });
                } else if (data.redirect) {
                    await loadPage(data.redirect);
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
                await refreshCurrentUser();
                await loadPage(data.redirect);
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



