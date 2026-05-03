document.addEventListener('DOMContentLoaded', () => {
    const slider = document.getElementById('promo-slider');
    const dots = document.querySelectorAll('.promo-dot');
    const slides = slider.children;
    
    if (!slider || dots.length === 0) return;

    let currentIndex = 0;
    const totalSlides = dots.length;
    let startX = 0;
    let currentTranslate = 0;
    let prevTranslate = 0;
    let isDragging = false;
    let slideInterval;

    function updateSlider() {
        slider.style.transition = "transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)";
        slider.style.transform = `translateX(-${currentIndex * 100}%)`;
        
        // Update Dots
        dots.forEach((dot, index) => {
            if (index === currentIndex) {
                dot.classList.add('bg-red-500', 'w-5');
                dot.classList.remove('bg-gray-200', 'w-1.5');
            } else {
                dot.classList.remove('bg-red-500', 'w-5');
                dot.classList.add('bg-gray-200', 'w-1.5');
            }
        });
    }

    function startAutoSlide() {
        slideInterval = setInterval(() => {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateSlider();
        }, 4000);
    }

    // Handle Swipe Manual
    slider.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
        clearInterval(slideInterval);
        slider.style.transition = "none";
    }, {passive: true});

    slider.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        const currentX = e.touches[0].clientX;
        const diff = currentX - startX;
        const move = (currentIndex * slider.offsetWidth) - diff;
        slider.style.transform = `translateX(-${move}px)`;
    }, {passive: true});

    slider.addEventListener('touchend', (e) => {
        isDragging = false;
        const endX = e.changedTouches[0].clientX;
        const diff = endX - startX;

        if (Math.abs(diff) > 50) {
            if (diff > 0 && currentIndex > 0) currentIndex--;
            else if (diff < 0 && currentIndex < totalSlides - 1) currentIndex++;
        }
        updateSlider();
        startAutoSlide();
    });

    // Support Mouse Drag (Optional for Desktop)
    slider.addEventListener('mousedown', (e) => {
        startX = e.pageX;
        isDragging = true;
        clearInterval(slideInterval);
        slider.style.transition = "none";
    });

    window.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        const diff = e.pageX - startX;
        const move = (currentIndex * slider.offsetWidth) - diff;
        slider.style.transform = `translateX(-${move}px)`;
    });

    window.addEventListener('mouseup', (e) => {
        if (!isDragging) return;
        isDragging = false;
        const diff = e.pageX - startX;
        if (Math.abs(diff) > 50) {
            if (diff > 0 && currentIndex > 0) currentIndex--;
            else if (diff < 0 && currentIndex < totalSlides - 1) currentIndex++;
        }
        updateSlider();
        startAutoSlide();
    });

    // Jalankan Awal
    updateSlider();
    startAutoSlide();
});