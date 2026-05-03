/**
 * why_Us.js - Logic for Why Us Modal
 */

function openModal(index) {
    const data = window.whyUsData ? window.whyUsData[index] : null;
    const modal = document.getElementById('whyUsModal');
    const content = document.getElementById('modalContent');

    if (data && modal) {
        // 1. Update Konten
        document.getElementById('modalImg').src = data.img;
        document.getElementById('modalTitle').innerText = data.title;
        document.getElementById('modalDesc').innerText = data.full_desc;
        document.getElementById('modalIcon').className = `fa-solid ${data.icon}`;

        // 2. Tampilkan Modal
        modal.classList.remove('hidden');

        // 3. Trigger Animasi (sedikit delay agar transisi jalan)
        setTimeout(() => {
            content.classList.remove('opacity-0', 'scale-90');
            content.classList.add('opacity-100', 'scale-100');
        }, 20);

        // 4. Lock Scroll
        document.body.style.overflow = 'hidden';
    }
}

function closeModal() {
    const modal = document.getElementById('whyUsModal');
    const content = document.getElementById('modalContent');

    if (modal) {
        // 1. Animasi Menghilang
        content.classList.remove('opacity-100', 'scale-100');
        content.classList.add('opacity-0', 'scale-90');

        // 2. Sembunyikan permanen setelah animasi selesai (300ms)
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 300);
    }
}