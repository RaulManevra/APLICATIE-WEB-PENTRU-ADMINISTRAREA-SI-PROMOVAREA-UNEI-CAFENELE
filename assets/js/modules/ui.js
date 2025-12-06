/**
 * UI Component Logic (Hero, Header)
 */
import { escapeHtml } from './utils.js';

const hero = document.getElementById("hero");

// ===== HERO SECTION UPDATE =====
export function updateHero(page) {
    if (page === "home") {
        const userText = window.CURRENT_USER ? `Welcome back, ${escapeHtml(window.CURRENT_USER)}!` : "Welcome to Mazi Coffee";
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
}

// ===== NAV LINK ACTIVE STATE =====
export function setActiveLink(page) {
    document.querySelectorAll(".nav-link").forEach(link => {
        link.classList.toggle("active", link.dataset.page === page);
    });
}

/**
 * Updates the header UI (Admin link, Profile Popup buttons) based on current session state.
 * Dynamically creates popup buttons if they are missing from the DOM.
 */
export function updateHeaderUI() {
    const roles = window.APP_CONFIG?.currentUserRoles || [];
    const isAdmin = roles.includes('admin');
    const isLoggedIn = !!window.CURRENT_USER;

    // 1. Toggle Admin Link
    let adminLinkAndLi = document.querySelector('.nav-link[data-page="admin"]')?.parentElement;
    if (isAdmin) {
        if (!adminLinkAndLi) {
            const ul = document.querySelector('.navbar ul');
            if (ul) {
                const contactLi = document.querySelector('.nav-link[data-page="contact"]')?.parentElement;
                if (contactLi) {
                    const li = document.createElement('li');
                    li.innerHTML = '<a href="?page=admin" class="nav-link" data-page="admin">Admin</a>';
                    contactLi.after(li);
                }
            }
        } else {
            adminLinkAndLi.style.display = '';
        }
    } else {
        if (adminLinkAndLi) adminLinkAndLi.style.display = 'none';
    }

    // 2. Toggle Popup Content (Login/Register vs Logout)
    let loginBtn = document.getElementById('popup-login');
    let registerBtn = document.getElementById('popup-register');
    let logoutBtn = document.getElementById('popup-logout');

    const popupContent = document.querySelector('.profile-popup-content');
    if (popupContent) {
        if (isLoggedIn) {
            // Logged IN: Hide auth buttons, Show Logout
            if (loginBtn) loginBtn.style.display = 'none';
            if (registerBtn) registerBtn.style.display = 'none';

            if (!logoutBtn) {
                const btn = document.createElement('button');
                btn.id = 'popup-logout';
                btn.className = 'popup-action';
                btn.dataset.page = 'logout';
                btn.innerText = 'Logout';
                popupContent.appendChild(btn);
            } else {
                logoutBtn.style.display = '';
            }
        } else {
            // Logged OUT: Show Login/Register, Hide Logout
            if (logoutBtn) logoutBtn.style.display = 'none';

            if (!loginBtn) {
                const btn = document.createElement('button');
                btn.id = 'popup-login';
                btn.className = 'popup-action';
                btn.dataset.page = 'login';
                btn.innerText = 'Log In';
                popupContent.appendChild(btn);
                loginBtn = btn;
            } else {
                loginBtn.style.display = '';
                if (!loginBtn.dataset.page) loginBtn.dataset.page = 'login';
            }

            if (!registerBtn) {
                const btn = document.createElement('button');
                btn.id = 'popup-register';
                btn.className = 'popup-action';
                btn.dataset.page = 'register';
                btn.innerText = 'Register';
                popupContent.appendChild(btn);
                registerBtn = btn;
            } else {
                registerBtn.style.display = '';
                if (!registerBtn.dataset.page) registerBtn.dataset.page = 'register';
            }
        }
    }
}
