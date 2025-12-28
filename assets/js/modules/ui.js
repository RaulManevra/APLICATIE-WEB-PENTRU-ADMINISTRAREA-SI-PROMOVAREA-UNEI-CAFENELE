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
    const adminLinkLi = document.getElementById('admin-link-li');
    if (adminLinkLi) {
        adminLinkLi.style.display = isAdmin ? '' : 'none';
    }

    // 2. Toggle Popup Content (Login/Register vs Logout)
    const authButtons = document.getElementById('auth-buttons');
    const profileButtons = document.getElementById('profile-buttons');

    // Profile Info Container (inside profile popup)
    let profileInfo = document.getElementById('profile-info');
    const popupContent = document.querySelector('.profile-popup-content');

    if (isLoggedIn) {
        // Logged IN: Show Profile Icon, Hide Login/Register
        if (authButtons) authButtons.style.display = 'none';
        if (profileButtons) profileButtons.style.display = '';

        if (popupContent) {
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
        }
    } else {
        // Logged OUT: Hide Profile Icon, Show Login/Register
        if (authButtons) authButtons.style.display = '';
        if (profileButtons) profileButtons.style.display = 'none';

        // Remove profile info from popup if it exists (cleanup)
        if (profileInfo) profileInfo.remove();
    }
}

/**
 * Initializes the navbar scroll effect.
 */
export function initNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;

    const handleScroll = () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    };

    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Check initial state
}

/**
 * Initializes the material design ripple effect on buttons.
 */
export function initRippleEffect() {
    const selector = '.hero-offer-btn, .auth-btn, button, .product-card .buy-now';

    // We use delegation on document body to handle dynamically added buttons (like in menu or loaded pages)
    document.addEventListener('click', function (e) {
        const target = e.target.closest(selector);

        if (target) {
            // Add utility class if missing (for overflow:hidden)
            if (!target.classList.contains('btn-ripple')) {
                target.classList.add('btn-ripple');
            }

            const circle = document.createElement('span');
            const diameter = Math.max(target.clientWidth, target.clientHeight);
            const radius = diameter / 2;

            const rect = target.getBoundingClientRect();

            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${e.clientX - rect.left - radius}px`;
            circle.style.top = `${e.clientY - rect.top - radius}px`;
            circle.classList.add('ripple');

            const ripple = target.getElementsByClassName('ripple')[0];
            if (ripple) {
                ripple.remove();
            }

            target.appendChild(circle);
        }
    });
}
