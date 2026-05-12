<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Fungsi pembantu untuk class aktif
function isActive($page, $dir, $current_page, $current_dir) {
    if ($page === $current_page && $dir === $current_dir) {
        return 'bg-yellow-400 text-slate-900 font-bold shadow-lg shadow-yellow-400/20';
    }
    return 'text-slate-400 hover:bg-white/5 hover:text-yellow-400';
}
?>

<style>
    #overlay { transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out; }
    #overlay.hidden { opacity: 0; visibility: hidden; display: block !important; }
    #overlay.visible { opacity: 1; visibility: visible; }
    
    .sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(250, 204, 21, 0.1); border-radius: 10px; }
</style>

<button onclick="openSidebar()" 
    class="fixed top-4 left-4 z-50 bg-yellow-400 text-slate-900 p-2.5 rounded-xl md:hidden shadow-lg shadow-yellow-400/30">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
    </svg>
</button>

<div id="overlay" onclick="closeSidebar()" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm hidden z-40 md:hidden"></div>

<aside id="sidebar" class="fixed md:static top-0 left-0 w-64 h-screen bg-slate-950 border-r border-white/5 transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-50 flex flex-col font-sans">

    <div class="p-8 mb-2 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-yellow-400 rounded-xl flex items-center justify-center shadow-lg shadow-yellow-400/20">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-slate-900">
                    <path d="M3.375 18.75h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125zM2.25 12c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5z" />
                </svg>
            </div>
            <div>
                <h1 class="text-white font-black text-xl tracking-tighter">MAHAYANA</h1>
                <span class="text-[10px] text-yellow-500 font-bold tracking-[0.2em] uppercase">Transport Panel</span>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 pb-4 space-y-1 sidebar-scroll">
        
        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest px-4 mt-6 mb-2">Main Menu</p>

        <a href="/mahayana/admin/dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= isActive('dashboard.php', 'admin', $current_page, $current_dir) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z" />
            </svg>
            Dashboard
        </a>

        <div x-data="{ open: <?= (in_array($current_dir, ['buses', 'rentals'])) ? 'true' : 'false' ?> }">
            <div @click="open = !open" 
                class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl cursor-pointer transition-all text-slate-400 hover:bg-white/5 hover:text-yellow-400 <?= (in_array($current_dir, ['buses', 'rentals'])) ? 'text-yellow-400' : '' ?>">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V4.625c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125H18M15 10.5H8.25V4.5H15v6z" />
                    </svg>
                    <span>Armada</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <div x-show="open" x-cloak class="ml-9 mt-1 space-y-1 border-l border-white/10">
                <a href="/mahayana/admin/buses/index.php" 
                   class="block py-2 px-4 text-sm transition-colors <?= isActive('index.php', 'buses', $current_page, $current_dir) ?> rounded-r-lg">
                   Shuttle
                </a>
                <a href="/mahayana/admin/rentals/index.php" 
                   class="block py-2 px-4 text-sm transition-colors <?= isActive('index.php', 'rentals', $current_page, $current_dir) ?> rounded-r-lg">
                   Sewa Armada
                </a>
            </div>
        </div>

        <a href="/mahayana/admin/bookings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= isActive('bookings.php', 'admin', $current_page, $current_dir) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25" />
            </svg>
            Laporan Booking
        </a>

        <a href="/mahayana/admin/routes/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= isActive('index.php', 'routes', $current_page, $current_dir) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-10.5v.75m.001 3v.75m0 3v.75m0 3V15" />
            </svg>
            Rute Perjalanan
        </a>

        <a href="/mahayana/admin/schedules/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= isActive('index.php', 'schedules', $current_page, $current_dir) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0" />
            </svg>
            Jadwal Operasional
        </a>

        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest px-4 mt-8 mb-2">Manajemen Kursi</p>

        <a href="/mahayana/admin/seats/index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= isActive('index.php', 'seats', $current_page, $current_dir) ?>">
            <i class="fa-solid fa-chair w-5 text-center"></i>
            Master Kursi
        </a>

        <a href="/mahayana/admin/seats/view_seats.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= isActive('view_seats.php', 'seats', $current_page, $current_dir) ?>">
            <i class="fa-solid fa-desktop w-5 text-center"></i>
            Monitoring Real-time
        </a>

        <a href="/mahayana/admin/promo/index.php" 
   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= isActive('index.php', 'promo', $current_page, $current_dir) ?>">
    
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round" 
        d="M9 14.25l6-6m4.5 6.75a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>

    Promo
</a>

    </nav>

    <div class="p-4 border-t border-white/5">
        <a href="/mahayana/auth_admin/logout.php" 
            class="flex items-center justify-center gap-2 w-full bg-red-500/10 text-red-500 py-3 rounded-xl hover:bg-red-500 hover:text-white transition-all font-bold text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6m-9 3l3 3m0 0l-3 3" />
            </svg>
            Keluar Sistem
        </a>
    </div>

</aside>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => overlay.classList.add('visible'), 10);
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.remove('visible');
        document.body.style.overflow = '';
        setTimeout(() => overlay.classList.add('hidden'), 300);
    }
</script>
