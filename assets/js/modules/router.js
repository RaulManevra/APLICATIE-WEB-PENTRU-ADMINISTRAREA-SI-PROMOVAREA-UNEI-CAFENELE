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
export async function loadPage(page, pushState = true) {
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
        throw new Error("Page not found");
    }

    if (page === "admin") {
        const roles = window.APP_CONFIG?.currentUserRoles || [];
        if (!roles.includes("admin")) {
            showModal("Nu esti autorizat să accesezi pagina de administrare.");
            setActiveLink("home");
            return loadPage("home");
        }
    }

    // Append timestamp to prevent caching of views
    const fetchUrl = `${url}?t=${new Date().getTime()}`;

    // 1. Start Fade Out of Current Page
    app.classList.add('page-transition-exit');

    // Create Promises
    let isFetched = false;
    const fetchPromise = safeFetch(fetchUrl)
        .then(res => res.text())
        .then(html => {
            isFetched = true;
            return html;
        })
        .catch(err => {
            console.error("Failed to load page:", err);
            // If error, return error error UI
            isFetched = true; // technically finished
            return "<h2>Error loading page</h2>";
        });

    // Wait for the exit animation (300ms)
    await new Promise(r => setTimeout(r, 300));

    // Function to yield to main thread
    const nextFrame = () => new Promise(resolve => requestAnimationFrame(resolve));

    // 2. Decision Point: Show Skeleton or New Content?
    if (!isFetched) {
        // Fetch is slow (>300ms). Show Skeleton.
        const { getSkeleton } = await import('./skeletons.js');

        // Render Skeleton
        app.innerHTML = getSkeleton(page);
        app.classList.remove('page-transition-exit');

        await nextFrame();
        app.classList.add('page-transition-enter'); // Fade In Skeleton

        // Wait for HTML to arrive
        const html = await fetchPromise;

        // Preload any new CSS files in the HTML to prevent FOUC
        await preloadStyles(html);

        // Swap Real Content Immediately
        app.innerHTML = html;
        app.classList.remove('page-transition-exit');

        await nextFrame();
        app.classList.add('page-transition-enter'); // Fade In Real Content

    } else {
        // Fetch was fast.
        const html = await fetchPromise;

        // Even if fast, we should preload styles to be safe?
        // Yes, otherwise FOUC happens immediately.
        await preloadStyles(html);

        // Swap Content
        app.innerHTML = html;
        app.classList.remove('page-transition-exit');

        await nextFrame();
        app.classList.add('page-transition-enter');
    }

    // 3. Cleanup & Initialize
    setTimeout(() => app.classList.remove('page-transition-enter'), 400);

    // Yield before creating scripts to separate layout from script exec
    await nextFrame();
    executeScripts(app);

    // Try to init slider if we are on home page
    initSlider();

    // Initialize scroll animations
    initAnimations();

    updateHero(page);
    setActiveLink(page);

    if (pushState) {
        history.pushState({ page }, "", `?page=${page}`);
    }
}

/**
 * Execute scripts found in a container.
 * innerHTML does not execute scripts for security reasons.
 * We must recreate them to run them.
 */
function executeScripts(container) {
    const scripts = container.querySelectorAll("script");

    // We need to execute them in order, specifically for dependencies (like Chart.js before admin.js)
    // However, simply loop-replacing them triggers async loads for external scripts by default.
    // We should recreate them and set .async = false to hint the browser (or handle loading manually).

    Array.from(scripts).forEach(oldScript => {
        const newScript = document.createElement("script");
        Array.from(oldScript.attributes).forEach(attr => {
            newScript.setAttribute(attr.name, attr.value);
        });

        // Force synchronous-like execution order for external scripts
        newScript.async = false;

        // Clone text content (for inline scripts)
        newScript.textContent = oldScript.textContent;

        // Logging for debug
        console.log("EXEC SCRIPT:", newScript.src || "inline script");

        // Replace old script with new one to trigger execution
        oldScript.parentNode.replaceChild(newScript, oldScript);
    });
}

/**
 * Detects <link rel="stylesheet"> tags in the HTML string and loads them.
 * Returns a promise that resolves when all new styles are loaded.
 */
async function preloadStyles(htmlString) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(htmlString, 'text/html');
    const links = Array.from(doc.querySelectorAll('link[rel="stylesheet"]'));

    if (links.length === 0) return;

    const promises = links.map(link => {
        const href = link.getAttribute('href');
        if (!href) return Promise.resolve();

        // Check if already present in document
        if (document.querySelector(`link[href="${href}"]`)) {
            return Promise.resolve();
        }

        return new Promise((resolve) => {
            const newLink = document.createElement('link');
            newLink.rel = 'stylesheet';
            newLink.href = href;
            newLink.onload = () => resolve();
            newLink.onerror = () => {
                console.warn(`Failed to preload CSS: ${href}`);
                resolve(); // Don't block forever
            };
            document.head.appendChild(newLink);
        });
    });

    // Wait for all compatible styles to load
    // We add a timeout so one bad link doesn't freeze the app forever
    const timeout = new Promise(r => setTimeout(r, 2000));
    await Promise.race([Promise.all(promises), timeout]);
}
