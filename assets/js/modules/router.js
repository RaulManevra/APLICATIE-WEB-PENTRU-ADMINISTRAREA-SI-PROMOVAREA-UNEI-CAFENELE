/**
 * Router Implementation
 */
import { safeFetch } from './api.js';
import { updateHero, setActiveLink } from './ui.js';

import { showModal } from './utils.js';

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

        if (page === "admin") {
            const roles = window.APP_CONFIG?.currentUserRoles || [];
            if (!roles.includes("admin")) {
                showModal("You are not authorized to access the admin dashboard.");
                setActiveLink("home");
                // Don't recurse infinitely if home is broken, but here it's fine
                return loadPage("home");
            }
        }

        // Append timestamp to prevent caching of views
        const fetchUrl = `${url}?t=${new Date().getTime()}`;

        safeFetch(fetchUrl)
            .then(res => res.text())
            .then(html => {
                app.innerHTML = html;
                executeScripts(app);
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

/**
 * Execute scripts found in a container.
 * innerHTML does not execute scripts for security reasons.
 * We must recreate them to run them.
 */
function executeScripts(container) {
    const scripts = container.querySelectorAll("script");
    scripts.forEach(oldScript => {
        const newScript = document.createElement("script");
        Array.from(oldScript.attributes).forEach(attr => {
            newScript.setAttribute(attr.name, attr.value);
        });

        // Clone text content (for inline scripts)
        newScript.textContent = oldScript.textContent;

        // Replace old script with new one to trigger execution
        oldScript.parentNode.replaceChild(newScript, oldScript);
    });
}
