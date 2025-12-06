/**
 * Router Implementation
 */
import { safeFetch } from './api.js';
import { updateHero, setActiveLink } from './ui.js';

const app = document.getElementById("app");

/**
 * Loads page content via AJAX and updates history state.
 * @param {string} page - The page identifier (e.g., 'home', 'login').
 * @param {boolean} pushState - Whether to push to browser history.
 */
export function loadPage(page, pushState = true) {
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
