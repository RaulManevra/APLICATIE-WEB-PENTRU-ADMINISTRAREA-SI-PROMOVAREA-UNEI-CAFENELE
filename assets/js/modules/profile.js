/**
 * Profile Popup Logic
 */
import { safeFetch } from './api.js';

let scrollCompApplied = false;
let previousBodyPaddingRight = '';

/**
 * Global function to close the profile popup.
 * Handles restoring body scroll/padding.
 */
export function closeProfilePopup() {
    const popup = document.getElementById('profile-popup');
    const backdrop = document.getElementById('profile-backdrop');
    const btn = document.getElementById('profile-btn');

    if (popup && !popup.hasAttribute('hidden')) {
        popup.setAttribute('hidden', '');
        if (backdrop) backdrop.classList.remove('active');
        if (btn) btn.setAttribute('aria-expanded', 'false');

        // Remove scroll compensation
        if (scrollCompApplied) {
            document.body.style.paddingRight = previousBodyPaddingRight;
            scrollCompApplied = false;
            previousBodyPaddingRight = '';
        }
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }
}

/**
 * Initializes the profile popup logic.
 * Binds toggle events and safe closing logic.
 */
export function initProfilePopup() {
    const btn = document.getElementById('profile-btn');
    const popup = document.getElementById('profile-popup');
    const closeBtn = document.getElementById('profile-close');

    if (!btn || !popup) return;

    // Ensure backdrop exists
    let backdrop = document.getElementById('profile-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'profile-backdrop';
        document.body.appendChild(backdrop);
    }

    // Check if hidden (helper)
    const isHidden = (el) => el.hasAttribute('hidden');
    const getScrollbarWidth = () => window.innerWidth - document.documentElement.clientWidth;

    function applyScrollComp() {
        const sbw = getScrollbarWidth();
        if (sbw > 0) {
            previousBodyPaddingRight = document.body.style.paddingRight || '';
            document.body.style.paddingRight = `${sbw}px`;
            scrollCompApplied = true;
        }
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    }

    async function updateReservationDisplay() {
        try {
            const container = document.getElementById('upcoming-res-container');
            if (!container) return;

            const res = await safeFetch('?page=reservation&action=get_upcoming');
            const data = await res.json();

            if (data.success && data.data) {
                const r = data.data;

                const nameEl = document.getElementById('upcoming-res-name');
                const tableEl = document.getElementById('upcoming-res-table');
                const timeEl = document.getElementById('upcoming-res-time');

                if (nameEl) nameEl.textContent = r.reservation_name || 'Guest';
                if (tableEl) tableEl.textContent = r.table_name || r.table_id;

                if (timeEl && r.reservation_time) {
                    const d = new Date(r.reservation_time);
                    // Format: "10 Jan, 15:30"
                    const day = d.getDate();
                    const month = d.toLocaleString('en-US', { month: 'short' });
                    const time = d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
                    timeEl.textContent = `${day} ${month}, ${time}`;
                }

                container.hidden = false;
            } else {
                container.hidden = true;
            }
        } catch (e) {
            console.error('Failed to update reservation display', e);
        }
    }

    function openPopup() {
        popup.removeAttribute('hidden');
        backdrop.classList.add('active');
        btn.setAttribute('aria-expanded', 'true');
        applyScrollComp();
        updateReservationDisplay();
    }

    // Toggle wrapper
    function togglePopup(e) {
        if (e && typeof e.stopPropagation === 'function') e.stopPropagation();
        isHidden(popup) ? openPopup() : closeProfilePopup();
    }

    // Bindings (One-time Init check)
    if (!btn._profileInit) {
        btn.addEventListener('click', togglePopup);
        backdrop.addEventListener('click', closeProfilePopup);

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!popup.contains(e.target) && e.target !== btn) closeProfilePopup();
        });

        // Close on Esc
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeProfilePopup();
        });

        // Ensure static buttons have data-page for global delegation
        // (Dynamic ones are handled by updateHeaderUI)
        const loginBtn = document.getElementById('popup-login');
        if (loginBtn && !loginBtn.dataset.page) loginBtn.dataset.page = 'login';

        const registerBtn = document.getElementById('popup-register');
        if (registerBtn && !registerBtn.dataset.page) registerBtn.dataset.page = 'register';

        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                closeProfilePopup();
            });
        }

        btn._profileInit = true;
    }
}
