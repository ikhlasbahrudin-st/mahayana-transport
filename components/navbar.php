<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../config/koneksi.php';

/* =========================
   TAMBAHAN: SYNC STATUS
========================= */
require_once __DIR__ . '/../config/helper.php';
syncTravelStatus($conn);


// halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);

// ===================== USER =====================
$user = null;
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    $user_id = (int)$user_id;
    $query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
    $user = mysqli_fetch_assoc($query);
}

// ===================== LOGIKA NOTIFIKASI (FIX) =====================
/**
 * Logika: Jika user sedang berada di halaman 'pesanan.php' atau 'inbox.php', 
 * kita simpan waktu kunjungan terakhir mereka ke dalam SESSION.
 * Jumlah notif hanya akan menghitung data yang LEBIH BARU dari waktu kunjungan tersebut.
 */

if ($current_page == 'pesanan.php') {
    $_SESSION['last_view_pesanan'] = date('Y-m-d H:i:s');
}
if ($current_page == 'inbox.php') {
    $_SESSION['last_view_inbox'] = date('Y-m-d H:i:s');
}

$total_notif_pesanan = 0;
$total_notif_inbox = 0;

if ($user_id) {
    // 1. Hitung notif Pesanan (Hanya yang belum dilihat)
    $last_pesanan = $_SESSION['last_view_pesanan'] ?? '2000-01-01 00:00:00';
    $q_pesanan = mysqli_query($conn, "
        SELECT COUNT(*) as total FROM bookings 
        WHERE user_id = $user_id 
        AND created_at > '$last_pesanan'
        AND status IN ('pending','success','settlement')
    ");
    $total_notif_pesanan = mysqli_fetch_assoc($q_pesanan)['total'] ?? 0;

    // 2. Hitung notif Inbox (Hanya yang belum dilihat)
    $last_inbox = $_SESSION['last_view_inbox'] ?? '2000-01-01 00:00:00';
    $q_inbox = mysqli_query($conn, "
        SELECT COUNT(*) as total FROM payments p
        JOIN bookings b ON p.group_code = b.group_code
        WHERE b.user_id = $user_id 
        AND p.created_at > '$last_inbox'
        AND p.status = 'pending'
    ");
    $total_notif_inbox = mysqli_fetch_assoc($q_inbox)['total'] ?? 0;
}

// ===================== FOTO =====================
$photoSmall = '';
$photoLarge = '';

if ($user && !empty($user['user_picture'])) {
    $photo = $user['user_picture'];
    if (strpos($photo, 'googleusercontent.com') !== false) {
        $photo = preg_replace('/=s\d+-c$/', '', $photo);
        $photoSmall = $photo . '=s64-c';
        $photoLarge = $photo . '=s200-c';
    } else {
        $photoSmall = $photo; $photoLarge = $photo;
    }
}

if (empty($photoSmall)) {
    $name = urlencode($user['fullname'] ?? 'User');
    $photoSmall = "https://ui-avatars.com/api/?name=$name&size=64&background=D4AF37&color=001F3F";
    $photoLarge = "https://ui-avatars.com/api/?name=$name&size=200&background=D4AF37&color=001F3F";
}
?>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<nav class="fixed bottom-0 left-0 right-0 bg-[#001F3F] border-t border-[#D4AF37]/30 flex justify-between items-center px-2 pb-3 pt-2 z-50 max-w-md mx-auto shadow-[0_-4px_15px_rgba(0,0,0,0.3)]">

    <a href="/mahayana/index.php"
       class="flex-1 flex flex-col items-center <?= ($current_page == 'index.php') ? 'text-[#D4AF37]' : 'text-gray-400' ?>">
        <i class="fa-solid fa-house text-xl"></i>
        <span class="text-[9px] mt-1 uppercase font-bold">Beranda</span>
    </a>

    <a href="/mahayana/pesanan.php"
       class="flex-1 flex flex-col items-center relative <?= ($current_page == 'pesanan.php') ? 'text-[#D4AF37]' : 'text-gray-400' ?>">
        <i class="fa-solid fa-receipt text-xl"></i>
        <?php if ($total_notif_pesanan > 0 && $current_page != 'pesanan.php'): ?>
            <span class="absolute top-0 right-5 bg-red-600 text-white text-[8px] px-1.5 py-0.5 rounded-full font-bold border-2 border-[#001F3F] animate-bounce">
                <?= $total_notif_pesanan ?>
            </span>
        <?php endif; ?>
        <span class="text-[9px] mt-1 uppercase font-bold">Pesanan</span>
    </a>

    <div class="flex-1 flex flex-col items-center relative -top-4">
        <a href="https://wa.me/6282220152005" target="_blank" class="active:scale-90 transition-transform">
            <div class="bg-[#D4AF37] w-14 h-14 rounded-full flex items-center justify-center text-[#001F3F] text-2xl border-4 border-[#001F3F] shadow-lg">
                <i class="fa-brands fa-whatsapp text-white"></i>
            </div>
        </a>
        <span class="text-[9px] text-[#D4AF37] mt-1 uppercase font-bold">Chat</span>
    </div>

    <a href="/mahayana/inbox.php"
       class="flex-1 flex flex-col items-center relative <?= ($current_page == 'inbox.php') ? 'text-[#D4AF37]' : 'text-gray-400' ?>">
        <i class="fa-solid fa-envelope text-xl"></i>
        <?php if ($total_notif_inbox > 0 && $current_page != 'inbox.php'): ?>
            <span class="absolute top-0 right-5 bg-red-600 text-white text-[8px] px-1.5 py-0.5 rounded-full font-bold border-2 border-[#001F3F] animate-bounce">
                <?= $total_notif_inbox ?>
            </span>
        <?php endif; ?>
        <span class="text-[9px] mt-1 uppercase font-bold">Inbox</span>
    </a>

    <div x-data="{ openProfile: false }" class="flex-1">
        <?php if ($user): ?>
            <button @click="openProfile = true" class="flex flex-col items-center w-full text-gray-400 active:opacity-50 transition-opacity">
                <img src="<?= $photoSmall ?>"
                     referrerpolicy="no-referrer"
                     class="w-6 h-6 rounded-full object-cover border border-[#D4AF37]/50"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['fullname'] ?? 'User') ?>&background=D4AF37&color=001F3F'">
                <span class="text-[9px] mt-1 uppercase font-bold">Akun</span>
            </button>

            <template x-teleport="body">
                <div x-show="openProfile" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
                    
                    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="openProfile = false"></div>

                    <div class="bg-white w-full max-w-xs rounded-[2.5rem] shadow-2xl relative z-10 overflow-hidden border border-[#D4AF37]/30">
                        <div class="bg-[#001F3F] text-center p-6 border-b border-[#D4AF37]/30 relative">
                            <button @click="openProfile = false" class="absolute top-4 right-4 text-white/50 hover:text-white">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            <img src="<?= $photoLarge ?>"
                                 class="w-20 h-20 mx-auto rounded-full border-4 border-[#D4AF37] mb-2 shadow-lg object-cover">
                            <h3 class="text-white font-bold text-lg leading-tight"><?= htmlspecialchars($user['fullname'] ?? '-') ?></h3>
                            <p class="text-[9px] text-[#D4AF37] uppercase font-black tracking-[0.2em] mt-1"><?= htmlspecialchars($user['role'] ?? 'User') ?></p>
                        </div>

                        <div class="p-6 space-y-4 bg-white">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-envelope text-gray-300 text-sm w-5"></i>
                                <div>
                                    <p class="text-[8px] text-gray-400 uppercase font-bold leading-none mb-1">Email</p>
                                    <p class="text-xs font-bold text-[#001F3F]"><?= htmlspecialchars($user['email'] ?? '-') ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 pb-2 border-b border-gray-50">
                                <i class="fa-solid fa-phone text-gray-300 text-sm w-5"></i>
                                <div>
                                    <p class="text-[8px] text-gray-400 uppercase font-bold leading-none mb-1">WhatsApp</p>
                                    <p class="text-xs font-bold text-[#001F3F]"><?= htmlspecialchars($user['phone'] ?? '-') ?></p>
                                </div>
                            </div>

                            <a href="/mahayana/auth_user/logout.php"
                               class="block text-center bg-red-600 text-white py-3 rounded-2xl font-bold shadow-lg shadow-red-100 active:scale-95 transition-transform text-xs uppercase tracking-widest">
                                Logout Account
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        <?php else: ?>
            <a href="/mahayana/auth_user/login.php" class="flex-1 flex flex-col items-center w-full text-gray-400">
                <i class="fa-solid fa-right-to-bracket text-xl"></i>
                <span class="text-[9px] mt-1 uppercase font-bold">Login</span>
            </a>
        <?php endif; ?>
    </div>
</nav>

<style>
    [x-cloak] { display: none !important; }
    .animate-bounce {
        animation: bounce 1s infinite;
    }
    @keyframes bounce {
        0%, 100% { transform: translateY(-25%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
        50% { transform: none; animation-timing-function: cubic-bezier(0,0,0.2,1); }
    }
</style>