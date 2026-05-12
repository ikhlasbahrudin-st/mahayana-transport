function filterNews(type, el) {
    const items = document.querySelectorAll('.news-item');
    const buttons = document.querySelectorAll('.filter-btn');

    // reset semua button
    buttons.forEach(btn => {
        btn.classList.remove('bg-red-600', 'text-white', 'shadow-md');
        btn.classList.add('bg-white', 'text-gray-500', 'border');
    });

    // aktif button yang diklik
    el.classList.add('bg-red-600', 'text-white', 'shadow-md');
    el.classList.remove('bg-white', 'text-gray-500', 'border');

    // filter item
    items.forEach(item => {
        if (type === 'all') {
            item.style.display = 'block';
        } else {
            item.style.display = (item.dataset.type === type) ? 'block' : 'none';
        }
    });
}