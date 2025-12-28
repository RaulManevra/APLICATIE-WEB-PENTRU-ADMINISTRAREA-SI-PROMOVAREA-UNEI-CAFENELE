/**
 * Home Slider Module
 * Encapsulates logic for the hero slider to allow clean start/stop.
 */

let sliderInterval = null;
let sliderEvents = [];

/**
 * Initializes the home slider if the element exists.
 * @returns {boolean} True if slider was found and initialized.
 */
export function initSlider() {
    const slider = document.getElementById('homeSlider');
    if (!slider) return false;

    const slides = Array.from(slider.querySelectorAll('.slide'));
    if (!slides.length) return false;

    // Stop any existing interval just in case
    stopSlider();

    let index = 0;
    const intervalTime = 4200;

    // Reset initial state
    slides.forEach(s => s.classList.remove('active'));
    slides[0].classList.add('active');

    function showSlide(i) {
        slides.forEach((slide, n) => {
            slide.classList.toggle('active', n === i);
        });
    }

    function startTimer() {
        if (sliderInterval) clearInterval(sliderInterval);
        sliderInterval = setInterval(() => {
            index = (index + 1) % slides.length;
            showSlide(index);
        }, intervalTime);
    }

    function stopTimer() {
        if (sliderInterval) clearInterval(sliderInterval);
        sliderInterval = null;
    }

    // Start
    startTimer();

    // Event Listeners (saved for cleanup, though checking element existence is usually enough)
    const onMouseEnter = () => stopTimer();
    const onMouseLeave = () => startTimer();

    slider.addEventListener('mouseenter', onMouseEnter);
    slider.addEventListener('mouseleave', onMouseLeave);

    // Track for cleanup (though stricter cleanup might not be strictly necessary if DOM is wiped, 
    // stopping the interval is the critical part)
    sliderEvents = [
        { element: slider, type: 'mouseenter', handler: onMouseEnter },
        { element: slider, type: 'mouseleave', handler: onMouseLeave }
    ];

    console.log("Slider initialized");
    return true;
}

/**
 * Stops the slider interval and cleans up.
 */
export function stopSlider() {
    if (sliderInterval) {
        clearInterval(sliderInterval);
        sliderInterval = null;
        console.log("Slider stopped");
    }

    // Optional: Remove listeners if we want to be very clean, 
    // but if the element is about to be removed from DOM, it's less critical 
    // than the interval. However, it's good practice.
    sliderEvents.forEach(({ element, type, handler }) => {
        if (element) element.removeEventListener(type, handler);
    });
    sliderEvents = [];
}
