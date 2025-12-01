// ===== GLOBAL REFERENCES =====
const app = document.getElementById("app");
const hero = document.getElementById("hero");


// ===== PAGE MAPPING =====
// Routes are now injected from index.php via window.APP_CONFIG.routes

// ===== SAFE FETCH WRAPPER =====
async function safeFetch(url, options = {}) {
    try {
        const res = await fetch(url, options);
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res;
    } catch (err) {
        console.error("Fetch error:", err);
        showModal("Connection error. Please check your internet connection and try again.");
        throw err; // Re-throw to let caller handle specific logic if needed
    }
}

// ===== HERO SECTION UPDATE =====
function updateHero(page) {
    // ===== PLACEHOLDER: start hero transition (e.g. fade out) =====
    // Example: hero.classList.add('fade-out');

    if (page === "home") {
        let userText = window.CURRENT_USER ? `Welcome back, ${escapeHtml(window.CURRENT_USER)}!` : "Welcome to Mazi Coffee";
        hero.innerHTML = `
            <div class="content-wrapper">
                <div class="bgimg">
                    <div class="text">${userText}</div>
                </div>
            </div>
        `;
    } else {
        hero.innerHTML = "";
    }

    // ===== PLACEHOLDER: end hero transition (e.g. fade in) =====
    // Example: setTimeout(() => hero.classList.remove('fade-out'), 200);
}

// ===== NAV LINK ACTIVE STATE =====
function setActiveLink(page) {
    document.querySelectorAll(".nav-link").forEach(link => {
        link.classList.toggle("active", link.dataset.page === page);
    });
}

// ===== SAFE HTML ESCAPE (for client-side) =====
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

// ===== LOAD PAGE CONTENT =====
function loadPage(page, pushState = true) {
    return new Promise((resolve, reject) => {
        const routes = window.APP_CONFIG?.routes || {};
        const url = routes[page];

        if (!url) {
            app.innerHTML = "<h2>404 Page not found</h2>";
            updateHero(page);
            setActiveLink(page);
            reject(new Error("Page not found"));
            return;
        }

        safeFetch(url)
            .then(res => res.text())
            .then(html => {
                app.innerHTML = html;
                updateHero(page);
                setActiveLink(page);

                if (pushState) {
                    history.pushState({ page }, "", `?page=${page}`);
                }
                resolve();
            })
            .catch(err => {
                console.error("Failed to load page:", err);
                app.innerHTML = "<h2>Error loading page</h2>";
                reject(err);
            });
    });
}

// ===== MODAL FUNCTION =====
function showModal(message) {
    let modal = document.getElementById("modal");
    if (!modal) {
        modal = document.createElement("div");
        modal.id = "modal";
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                <p id="modal-msg"></p>
            </div>`;
        document.body.appendChild(modal);
        modal.querySelector(".close").onclick = () => modal.style.display = "none";

        Object.assign(modal.style, {
            position: "fixed",
            top: 0,
            left: 0,
            width: "100%",
            height: "100%",
            background: "rgba(0,0,0,0.5)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 9999,
            display: "none"
        });

        const content = modal.querySelector(".modal-content");
        Object.assign(content.style, {
            background: "#fff",
            padding: "20px",
            borderRadius: "8px",
            minWidth: "300px",
            textAlign: "center",
            position: "relative"
        });

        const closeBtn = modal.querySelector(".close");
        Object.assign(closeBtn.style, {
            position: "absolute",
            top: "5px",
            right: "10px",
            cursor: "pointer",
            fontSize: "20px"
        });

        // Close when clicking outside the content
        modal.addEventListener("click", e => {
            if (e.target === modal) modal.style.display = "none";
        });
        // attach close button behavior (will use closeModal defined below)
        closeBtn.addEventListener("click", closeModal);
    }

    // centralized close handler that also removes the Escape listener
    function closeModal() {
        if (!modal) return;
        modal.style.display = "none";
        // remove escape listener if attached
        if (modal._escHandler) {
            document.removeEventListener("keydown", modal._escHandler);
            modal._escHandler = null;
        }
    }

    // attach Escape handler (ensure only one listener is active)
    if (!modal._escHandler) {
        modal._escHandler = function (e) {
            if (e.key === "Escape" || e.key === "Esc") {
                closeModal();
            }
        };
        document.addEventListener("keydown", modal._escHandler);
    }

    // set message and show
    const msgEl = document.getElementById("modal-msg");
    if (msgEl) msgEl.innerText = message;
    modal.style.display = "flex";

    // move focus into modal for accessibility
    const content = modal.querySelector(".modal-content");
    if (content) content.focus?.();


    document.getElementById("modal-msg").innerText = message;
    modal.style.display = "flex";
}

// ===== REFRESH CURRENT USER FROM SERVER (updates window.CURRENT_USER and hero) =====
async function refreshCurrentUser() {
    try {
        // safeFetch handles errors internally (showing modal if needed), 
        // but for background updates we might want to suppress the modal?
        // For now, let's use standard fetch here to avoid annoying modals on background checks,
        // OR use safeFetch but catch the error silently.
        const res = await fetch('core/session.php', { cache: 'no-store' });
        if (!res.ok) return;
        const data = await res.json();
        window.CURRENT_USER = data.username ?? null;
        // Update hero immediately if on home
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = urlParams.get("page") || 'home';
        updateHero(currentPage);
    } catch (err) {
        // silently ignore; do not break UX
        console.error('refreshCurrentUser error', err);
    }
}

// ===== HANDLE FORM SUBMISSION VIA AJAX =====
document.addEventListener("submit", async e => {
    const form = e.target;

    if (form.matches("form[action*='controllers/register_handler.php'], form[action*='controllers/login_handler.php']")) {
        e.preventDefault();

        const formData = new FormData(form);
        const action = form.getAttribute("action");

        try {
            const res = await safeFetch(action, { method: "POST", body: formData });
            const data = await res.json();

            if (data.success) {
                // Refresh server session info (username) before rendering hero
                await refreshCurrentUser();

                // ===== PLACEHOLDER: optional animation before navigation =====
                await loadPage(data.redirect);
                // ===== PLACEHOLDER: optional animation after navigation =====
            } else {
                showModal(data.message);
            }
        } catch (err) {
            console.error(err);
            // Modal already shown by safeFetch if network error, 
            // but if json() fails or other logic, we catch here.
            // If safeFetch threw, it already showed modal, so maybe check?
            // Simple approach: just log. safeFetch covers network. 
            // If data.success is false, we handled it.
        }
    }
});

// ===== HANDLE NAVIGATION CLICK =====
document.addEventListener("click", async e => {
    if (e.target.classList.contains("nav-link")) {
        e.preventDefault();
        const page = e.target.dataset.page;

        if (page === "logout") {
            try {
                const res = await safeFetch('controllers/logout.php', { method: 'GET' });
                const data = await res.json();
                if (data.success) {
                    // Refresh user (should become null)
                    await refreshCurrentUser();
                    // ===== PLACEHOLDER: optional logout animation =====
                    await loadPage(data.redirect);
                } else {
                    showModal("Logout failed.");
                }
            } catch (err) {
                console.error(err);
                // safeFetch shows modal on network error
            }
            return;
        }

        // normal navigation
        await loadPage(page);
    }
});

// ===== HANDLE BACK/FORWARD BUTTONS =====
window.addEventListener("popstate", e => {
    if (e.state && e.state.page) {
        loadPage(e.state.page, false);
    }
});

// ===== INITIAL PAGE LOAD =====
const urlParams = new URLSearchParams(window.location.search);
loadPage(urlParams.get("page") || "home", false);

// ===== POP UP PROFILE MENU =====
function initProfilePopup() {
    const btn = document.getElementById('profile-btn');
    const popup = document.getElementById('profile-popup');
    const loginBtn = document.getElementById('popup-login');
    const registerBtn = document.getElementById('popup-register');
    const closeBtn = document.getElementById('profile-close');

    if (!btn || !popup) return;

    let backdrop = document.getElementById('profile-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'profile-backdrop';
        document.body.appendChild(backdrop);
    }

    let scrollCompApplied = false;
    let previousBodyPaddingRight = '';

    const getScrollbarWidth = () => window.innerWidth - document.documentElement.clientWidth;
    const isHidden = (el) => el.hasAttribute('hidden');

    function applyScrollComp() {
        const sbw = getScrollbarWidth();
        if (sbw > 0) {
            previousBodyPaddingRight = document.body.style.paddingRight || '';
            document.body.style.paddingRight = `${sbw}px`;
            scrollCompApplied = true;
        }
        // prevent page scroll while modal is open
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    }

    function removeScrollComp() {
        if (scrollCompApplied) {
            document.body.style.paddingRight = previousBodyPaddingRight;
            scrollCompApplied = false;
            previousBodyPaddingRight = '';
        }
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }

    function openPopup() {
        popup.removeAttribute('hidden');
        backdrop.classList.add('active');
        btn.setAttribute('aria-expanded', 'true');
        applyScrollComp();
    }

    function closePopup() {
        popup.setAttribute('hidden', '');
        backdrop.classList.remove('active');
        btn.setAttribute('aria-expanded', 'false');
        removeScrollComp();
    }

    function togglePopup(e) {
        if (e && typeof e.stopPropagation === 'function') e.stopPropagation();
        isHidden(popup) ? openPopup() : closePopup();
    }

    if (!btn._profileInit) {
        btn.addEventListener('click', togglePopup);

        backdrop.addEventListener('click', closePopup);

        // close when clicking outside
        document.addEventListener('click', function (e) {
            if (!popup.contains(e.target) && e.target !== btn) closePopup();
        });

        // close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closePopup();
        });

        // Redirect actions
        if (loginBtn) loginBtn.addEventListener('click', function () { window.location.href = '?page=login'; });
        if (registerBtn) registerBtn.addEventListener('click', function () { window.location.href = '?page=register'; });

        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                closePopup();
            });
        }

        btn._profileInit = true;
    }
}

// call on DOMContentLoaded and also immediately if DOM already parsed
document.addEventListener('DOMContentLoaded', initProfilePopup);
if (document.readyState === 'interactive' || document.readyState === 'complete') {
    initProfilePopup();
}
document.addEventListener('DOMContentLoaded', function () {
    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';
});