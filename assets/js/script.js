// ===== GLOBAL REFERENCES =====
const app = document.getElementById("app");
const hero = document.getElementById("hero");

// CURRENT_USER is set initially by index.php as window.CURRENT_USER
// It may be null or a string username.

// ===== PAGE MAPPING =====
const pagePaths = {
    home: 'views/pages/home.php',
    about: 'views/pages/about.php',
    menu: 'views/pages/menu.php',
    contact: 'views/pages/contact.php',
    login: 'views/auth/login.php',
    register: 'views/auth/register.php'
};

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
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

// ===== LOAD PAGE VIA AJAX (returns a Promise) =====
function loadPage(page, pushState = true) {
    return new Promise((resolve, reject) => {
        // ===== PLACEHOLDER: Start page transition / spinner animation here =====
        // e.g. showSpinner();

        const path = pagePaths[page] || `views/pages/${page}.php`; // Fallback or 404

        fetch(path)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.text();
            })
            .then(html => {
                app.innerHTML = html;
                updateHero(page);
                setActiveLink(page);

                if (pushState) {
                    history.pushState({ page }, "", `?page=${page}`);
                }

                // ===== PLACEHOLDER: End page transition / fade-in animation here =====
                // e.g. hideSpinner();

                resolve();
            })
            .catch(() => {
                app.innerHTML = "<h2>404 Page not found</h2>";
                reject();
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
    }

    document.getElementById("modal-msg").innerText = message;
    modal.style.display = "flex";
}

// ===== REFRESH CURRENT USER FROM SERVER (updates window.CURRENT_USER and hero) =====
async function refreshCurrentUser() {
    try {
        const res = await fetch('core/session.php', { cache: 'no-store' });
        if (!res.ok) return; // keep existing user if request fails
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
            const res = await fetch(action, { method: "POST", body: formData });
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
            showModal("An error occurred. Please try again.");
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
                const res = await fetch('controllers/logout.php', { method: 'GET' });
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
                showModal("Logout failed. Try again.");
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

    const isHidden = (el) => el.hasAttribute('hidden');

    function openPopup() {
        popup.removeAttribute('hidden');
        backdrop.classList.add('active');
        btn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function closePopup() {
        popup.setAttribute('hidden', '');
        backdrop.classList.remove('active');
        btn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
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
        if (loginBtn) {
            loginBtn.addEventListener('click', function () {
                window.location.href = '?page=login';
            });
        }
        if (registerBtn) {
            registerBtn.addEventListener('click', function () {
                window.location.href = '?page=register';
            });
        }

        // Close button inside popup
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