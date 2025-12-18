/**
 * Mazi Coffee - Main Entry Point
 */
import { safeFetch } from './modules/api.js';
import { loadPage } from './modules/router.js';
import { refreshCurrentUser } from './modules/auth.js';
import { initProfilePopup, closeProfilePopup } from './modules/profile.js';
import { showModal } from './modules/utils.js';
import { updateHeaderUI } from './modules/ui.js';

// ===== EVENT LISTENERS =====

// 1. Form Submission (Login/Register)
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

// 2. Navigation Click Handler (delegation for .nav-link and .popup-action)
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

// 3. History State Manager
window.addEventListener("popstate", e => {
    if (e.state && e.state.page) {
        loadPage(e.state.page, false);
    }
});

// ... imports at top ...

// ... other imports

// 4. Initial Load
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


