/**
 * Logika Slider Bhisa: Otomatis & Manual Swipe
 */
document.addEventListener('DOMContentLoaded', () => {
    const slider = document.getElementById('slider');
    const dots = document.querySelectorAll('.dot');
    const container = slider.parentElement;
    
    if (!slider || dots.length === 0) return;

    let currentIndex = 0;
    const totalSlides = dots.length;
    let startX = 0;
    let isDragging = false;
    let slideInterval;

    // Fungsi utama update posisi
    function updateSlider() {
        slider.style.transition = "transform 0.5s ease-in-out";
        slider.style.transform = `translateX(-${currentIndex * 100}%)`;
        
        dots.forEach((dot, index) => {
            if (index === currentIndex) {
                dot.classList.add('bg-white', 'w-4');
                dot.classList.remove('bg-white/50', 'w-2');
            } else {
                dot.classList.remove('bg-white', 'w-4');
                dot.classList.add('bg-white/50', 'w-2');
            }
        });
    }

    // Fungsi geser otomatis
    function startAutoSlide() {
        slideInterval = setInterval(() => {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateSlider();
        }, 3000);
    }

    function stopAutoSlide() {
        clearInterval(slideInterval);
    }

    // Logika Swipe / Geser Manual
    const touchStart = (e) => {
        startX = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
        isDragging = true;
        stopAutoSlide();
        slider.style.transition = "none"; // Matikan transisi saat ditarik tangan
    };

    const touchMove = (e) => {
        if (!isDragging) return;
        const currentX = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
        const diff = currentX - startX;
        // Memberikan efek geser sedikit mengikuti jari
        const move = (currentIndex * container.offsetWidth) - diff;
        slider.style.transform = `translateX(-${move}px)`;
    };

    const touchEnd = (e) => {
        if (!isDragging) return;
        const endX = e.type.includes('mouse') ? e.pageX : e.changedTouches[0].clientX;
        const diff = endX - startX;

        // Jika geseran lebih dari 50px, pindah slide
        if (Math.abs(diff) > 50) {
            if (diff > 0 && currentIndex > 0) {
                currentIndex--;
            } else if (diff < 0 && currentIndex < totalSlides - 1) {
                currentIndex++;
            }
        }
        
        isDragging = false;
        updateSlider();
        startAutoSlide();
    };

    // Event Listeners untuk Mobile (Touch)
    container.addEventListener('touchstart', touchStart, {passive: true});
    container.addEventListener('touchmove', touchMove, {passive: true});
    container.addEventListener('touchend', touchEnd);

    // Event Listeners untuk Desktop (Mouse)
    container.addEventListener('mousedown', touchStart);
    window.addEventListener('mousemove', touchMove);
    window.addEventListener('mouseup', touchEnd);

    // Mencegah gambar ikut ditarik (ghost image)
    slider.querySelectorAll('img').forEach(img => {
        img.addEventListener('dragstart', (e) => e.preventDefault());
    });

    // Jalankan awal
    updateSlider();
    startAutoSlide();
});