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
    const userData = window.APP_CONFIG?.currentUserData || {};

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

    // Profile Info Container
    let profileInfo = document.getElementById('profile-info');

    const popupContent = document.querySelector('.profile-popup-content');
    if (popupContent) {
        if (isLoggedIn) {
            // Logged IN: Hide auth buttons, Show Info + Logout
            if (loginBtn) loginBtn.style.display = 'none';
            if (registerBtn) registerBtn.style.display = 'none';

            // Create Profile Info if missing
            if (!profileInfo) {
                profileInfo = document.createElement('div');
                profileInfo.id = 'profile-info';
                profileInfo.style.marginBottom = '15px';
                profileInfo.style.textAlign = 'center';
                popupContent.prepend(profileInfo); // Add at top
            }
            // Update Profile Info Content
            const avatarUrl = userData.profile_picture || 'assets/public/default.png';
            const points = userData.loyalty_points || 0;
            profileInfo.innerHTML = `
                <img id="profile-pic-trigger" src="${escapeHtml(avatarUrl)}" alt="Profile" style="width:60px;height:60px;border-radius:50%;margin-bottom:10px;object-fit:cover;cursor:pointer;">
                <div style="font-weight:bold;margin-bottom:5px;">${escapeHtml(userData.username)}</div>
                <div style="font-size:0.9em;color:#666;">Loyalty Points: <span style="color:#d4a373;font-weight:bold;">${points}</span></div>
                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #eee;">
             `;

            // Add click listener for profile picture
            setTimeout(() => {
                const picTrigger = document.getElementById('profile-pic-trigger');
                if (picTrigger) {
                    picTrigger.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (confirm("Do you want to change your profile picture?")) {
                            import('./profile.js').then(p => p.closeProfilePopup());

                            // Fetch and show modal
                            import('./api.js').then(({ safeFetch }) => {
                                const routes = window.APP_CONFIG?.routes || {};
                                const url = routes['profile_picture_upload'];
                                if (url) {
                                    safeFetch(url)
                                        .then(res => res.text())
                                        .then(html => {
                                            import('./utils.js').then(({ showContentModal }) => {
                                                showContentModal(html);
                                            });
                                        })
                                        .catch(err => console.error("Failed to load upload form", err));
                                } else {
                                    console.error("Route for profile_picture_upload not found");
                                }
                            });
                        }
                    });
                }
            }, 0);

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
            // Logged OUT: Remove Info, Show Login/Register, Hide Logout
            if (profileInfo) profileInfo.remove();
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
