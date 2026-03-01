/**
 * Animation Logic
 * Handles scroll animations using IntersectionObserver.
 */

/**
 * Initializes scroll animations for elements with class .animate-on-scroll
 */
let observer;

/**
 * Initializes scroll animations for elements with class .animate-on-scroll
 */
export function initAnimations() {
    // Disconnect previous observer if it exists
    if (observer) {
        observer.disconnect();
    }

    const observerOptions = {
        root: null, // viewport
        rootMargin: '0px',
        threshold: 0.1 // trigger when 10% visible
    };

    observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                obs.unobserve(entry.target); // run once
            }
        });
    }, observerOptions);

    const elements = document.querySelectorAll('.animate-on-scroll');
    elements.forEach(el => observer.observe(el));

    // Also handle elements that might already be visible immediately
    elements.forEach(el => {
        if (isElementInViewport(el)) {
            el.classList.add('visible');
            observer.unobserve(el);
        }
    });

    console.log(`Initialized animations for ${elements.length} elements.`);
}

function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}
