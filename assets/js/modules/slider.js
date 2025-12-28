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

    // Controls
    const prevBtn = document.querySelector('.arrow.prev');
    const nextBtn = document.querySelector('.arrow.next');
    const dotsContainer = document.querySelector('.dots');

    // Stop existing to be safe
    stopSlider();

    let index = 0;
    const intervalTime = 5000; // slightly longer for better UX

    // Initialize dots
    if (dotsContainer) {
        dotsContainer.innerHTML = '';
        slides.forEach((_, i) => {
            const dot = document.createElement('span');
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => {
                manualSlide(i);
            });
            dotsContainer.appendChild(dot);
        });
    }
    const dots = dotsContainer ? Array.from(dotsContainer.children) : [];

    // Reset state
    slides.forEach(s => s.classList.remove('active'));
    slides[0].classList.add('active');

    function showSlide(i) {
        // Wrap around
        if (i >= slides.length) index = 0;
        else if (i < 0) index = slides.length - 1;
        else index = i;

        // Update slides
        slides.forEach((slide, n) => {
            slide.classList.toggle('active', n === index);
        });

        // Update dots
        dots.forEach((dot, n) => {
            dot.classList.toggle('active', n === index);
        });
    }

    function nextSlide() {
        showSlide(index + 1);
    }

    function prevSlide() {
        showSlide(index - 1);
    }

    function manualSlide(i) {
        stopTimer(); // specific stop for interaction
        showSlide(i);
        startTimer(); // restart
    }

    function startTimer() {
        if (sliderInterval) clearInterval(sliderInterval);
        sliderInterval = setInterval(nextSlide, intervalTime);
    }

    function stopTimer() {
        if (sliderInterval) clearInterval(sliderInterval);
        sliderInterval = null;
    }

    // Auto-start
    startTimer();

    // Event Listeners
    const onMouseEnter = () => stopTimer();
    const onMouseLeave = () => startTimer();

    // Manual Navigation Listeners
    const onNextClick = () => {
        stopTimer();
        nextSlide();
        startTimer();
    };
    const onPrevClick = () => {
        stopTimer();
        prevSlide();
        startTimer();
    };

    slider.addEventListener('mouseenter', onMouseEnter);
    slider.addEventListener('mouseleave', onMouseLeave);

    if (nextBtn) nextBtn.addEventListener('click', onNextClick);
    if (prevBtn) prevBtn.addEventListener('click', onPrevClick);

    // Track for cleanup
    sliderEvents = [
        { element: slider, type: 'mouseenter', handler: onMouseEnter },
        { element: slider, type: 'mouseleave', handler: onMouseLeave },
        { element: nextBtn, type: 'click', handler: onNextClick },
        { element: prevBtn, type: 'click', handler: onPrevClick }
    ];

    console.log("Slider initialized with controls");
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
