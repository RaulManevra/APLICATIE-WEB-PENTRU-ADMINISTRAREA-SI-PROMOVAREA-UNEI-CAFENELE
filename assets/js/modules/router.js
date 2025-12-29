/**
 * Router Implementation
 */
import { safeFetch } from './api.js';
import { updateHero, setActiveLink, initNavbarScroll, initRippleEffect } from './ui.js';
import { initSlider, stopSlider } from './slider.js';
import { initAnimations } from './animations.js';

import { showModal } from './utils.js';

const app = document.getElementById("app");

// Initialize global UI effects once
initNavbarScroll();
initRippleEffect();

/**
 * Loads page content via AJAX and updates history state.
 * @param {string} page - The page identifier (e.g., 'home', 'login').
 * @param {boolean} pushState - Whether to push to browser history.
 */
export function loadPage(page, pushState = true) {
    return new Promise((resolve, reject) => {
        // ALWAYS stop the slider when navigating
        stopSlider();

        // Hide Navbar on Admin Page
        const navbar = document.querySelector('.header'); // or .navbar
        if (page === 'admin') {
            if (navbar) navbar.style.display = 'none';
        } else {
            if (navbar) navbar.style.display = 'block';
        }

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

        // 1. Start Fade Out
        app.classList.add('page-transition-exit');

        // Wait for animation to finish (300ms matches CSS) AND fetch to complete
        const animationPromise = new Promise(r => setTimeout(r, 300));
        const fetchPromise = safeFetch(fetchUrl).then(res => res.text());

        Promise.all([animationPromise, fetchPromise])
            .then(([_, html]) => {
                // 2. Swap Content
                app.innerHTML = html;
                app.classList.remove('page-transition-exit');

                // 3. Start Fade In
                app.classList.add('page-transition-enter');
                setTimeout(() => app.classList.remove('page-transition-enter'), 400); // Clean up after anim

                executeScripts(app);

                // Try to init slider if we are on home page (or if the page has a slider)
                initSlider();

                // Initialize scroll animations
                initAnimations();

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

                // RESTORE VISIBILITY
                app.classList.remove('page-transition-exit');
                app.classList.add('page-transition-enter');
                setTimeout(() => app.classList.remove('page-transition-enter'), 400);

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
