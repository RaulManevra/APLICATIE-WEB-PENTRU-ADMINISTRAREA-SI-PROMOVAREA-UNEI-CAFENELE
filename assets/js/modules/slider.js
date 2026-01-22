/**
 * Home Slider Module
 * Encapsulates logic for the hero slider to allow clean start/stop.
 */

let sliderInterval = null;
let sliderEvents = [];
let sliderData = []; // Store fetched data

/**
 * Initializes the home slider if the element exists.
 * @returns {boolean} True if slider was found and initialized.
 */
export async function initSlider() {
    const slider = document.getElementById('homeSlider');
    if (!slider) return false;

    // Check if slides exist, if not fetch them
    let slides = Array.from(slider.querySelectorAll('.slide'));
    sliderData = []; // Reset

    if (slides.length === 0) {
        try {
            const res = await fetch('controllers/get_public_slides.php');
            const data = await res.json();

            if (data.success && data.data && data.data.length > 0) {
                sliderData = data.data; // Store for later text updates

                // Render FIRST slide immediately (Critical Path)
                const s0 = data.data[0];
                const img0 = document.createElement('img');
                img0.className = 'slide active';
                img0.src = s0.image_path;
                img0.alt = s0.title || 'Slide';
                slider.appendChild(img0);

                slides = [img0]; // Init with first

                // Lazy load the rest after page settles
                setTimeout(() => {
                    data.data.slice(1).forEach((s, i) => {
                        const img = document.createElement('img');
                        img.className = 'slide';
                        img.src = s.image_path;
                        img.alt = s.title || 'Slide';
                        slider.appendChild(img);
                        slides.push(img);
                    });

                    // Re-query to ensure order (optional, but safe)
                    // slides = Array.from(slider.querySelectorAll('.slide')); 

                }, 3000); // 3-second delay for background loading

            } else {
                console.warn("No slides found in DB.");
                return false;
            }
        } catch (err) {
            console.error("Failed to fetch slides:", err);
            return false;
        }
    }

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

        // Update Content with Animation
        const wrapper = document.getElementById('slide-content-wrapper');
        const titleEl = document.getElementById('hero-title');
        const subtitleEl = document.getElementById('hero-subtitle');
        const descEl = document.getElementById('hero-text');
        const btnEl = document.getElementById('hero-btn');

        if (wrapper) {
            // Fade out
            wrapper.style.opacity = '0';
            wrapper.style.transform = 'translateY(10px)';
            wrapper.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

            setTimeout(() => {
                // Update content
                if (sliderData && sliderData[index]) {
                    const s = sliderData[index];
                    if (titleEl) titleEl.innerHTML = safe(s.title || 'Welcome');
                    if (subtitleEl) subtitleEl.innerHTML = safe(s.subtitle || '');
                    if (descEl) descEl.innerHTML = safe(s.description || '');

                    if (btnEl) {
                        if (s.is_button_visible == "1") {
                            btnEl.style.display = 'inline-block';
                            btnEl.innerText = s.button_text || 'View Menu';
                            btnEl.setAttribute('href', s.button_link || '?page=menu');
                            btnEl.setAttribute('data-page', (s.button_link || '').replace('?page=', ''));
                        } else {
                            btnEl.style.display = 'none';
                        }
                    }
                }

                // Fade in
                wrapper.style.opacity = '1';
                wrapper.style.transform = 'translateY(0)';
            }, 300);
        }
    }

    function safe(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
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

    sliderEvents.forEach(({ element, type, handler }) => {
        if (element) element.removeEventListener(type, handler);
    });
    sliderEvents = [];
}
