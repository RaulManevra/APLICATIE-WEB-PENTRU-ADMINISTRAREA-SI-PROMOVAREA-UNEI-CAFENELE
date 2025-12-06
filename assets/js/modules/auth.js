/**
 * Auth Logic
 */
import { updateHeaderUI, updateHero } from './ui.js';

/**
 * Refreshes current user data from the server and updates UI.
 * Used after login/logout to sync state without full reload.
 */
export async function refreshCurrentUser() {
    try {
        const res = await fetch(`core/session.php?t=${Date.now()}`, {
            cache: 'no-store',
            credentials: 'same-origin'
        });
        if (!res.ok) return;
        const data = await res.json();

        window.CURRENT_USER = data.username ?? null;
        console.log("Session refreshed:", data);

        if (window.APP_CONFIG) {
            window.APP_CONFIG.currentUserRoles = data.roles || [];
            window.APP_CONFIG.currentUser = window.CURRENT_USER;
        }

        updateHeaderUI();

        // Update hero if on home page
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = urlParams.get("page") || 'home';
        updateHero(currentPage);
    } catch (err) {
        console.error('refreshCurrentUser error', err);
    }
}
