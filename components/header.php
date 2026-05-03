<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../config/koneksi.php';

$user = null;
if (isset($_SESSION['user_id'])) {
    $id = (int) $_SESSION['user_id'];
    $query = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
    $user = mysqli_fetch_assoc($query);
}

// ===== FIX FOTO =====
$photoHeader = '';
if ($user && !empty($user['user_picture'])) {
    $photo = $user['user_picture'];
    if (strpos($photo, 'googleusercontent.com') !== false) {
        $photo = preg_replace('/=s\d+-c$/', '', $photo);
        $photoHeader = $photo . '=s64-c'; 
    } else {
        $photoHeader = $photo;
    }
}

$nameForAvatar = urlencode($user['fullname'] ?? 'User');
$defaultAvatar = "https://ui-avatars.com/api/?name=$nameForAvatar&background=D4AF37&color=0a192f&size=64";

if (empty($photoHeader)) {
    $photoHeader = $defaultAvatar;
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,700;1,900&family=Cinzel:wght@400;700&display=swap');

    [x-cloak] { display: none !important; }
    
    .font-latin-luxury {
        font-family: 'Playfair Display', serif;
        font-style: italic;
    }

    .font-cinzel {
        font-family: 'Cinzel', serif;
    }

    .nav-royal-bg {
        background: linear-gradient(to right, #0a192f, #112240); 
        border-bottom: 1px solid #D4AF37; /* Border lebih tipis */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
    }

    .gold-text {
        background: linear-gradient(to bottom, #f1d592, #D4AF37, #b8860b);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 1px 1px rgba(0,0,0,0.3));
    }

    .avatar-frame {
        background: linear-gradient(135deg, #f1d592, #D4AF37, #b8860b);
        padding: 1.5px; 
        border-radius: 9999px;
    }

    .btn-restricted {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(212, 175, 55, 0.2);
        transition: all 0.3s ease;
    }
</style>

<header class="nav-royal-bg px-4 md:px-8 py-2.5 flex justify-between items-center sticky top-0 z-40">
    
    <div class="flex items-center gap-3">
        <div class="flex items-center justify-center w-8 h-8 border border-[#D4AF37]/30 rounded-full">
            <span class="font-cinzel text-[#D4AF37] text-xs font-bold">M</span>
        </div>

        <div class="flex flex-col">
            <h1 class="font-latin-luxury text-xl md:text-2xl gold-text leading-tight tracking-normal">
                Mahayana
            </h1>
            <span class="h-[1px] w-8 bg-[#D4AF37]/30"></span>
        </div>
    </div>

    <div class="flex items-center">
        
        <?php if ($user): ?>
            <div class="flex items-center gap-3 pl-4 border-l border-white/5">
                <div class="text-right hidden sm:block">
                    <p class="font-latin-luxury text-sm text-white/90 leading-none">
                        <?= explode(' ', htmlspecialchars($user['fullname']))[0]; ?>
                    </p>
                </div>
                
                <div class="relative group">
                    <div class="avatar-frame w-8 h-8 md:w-9 md:h-9">
                        <div class="w-full h-full rounded-full overflow-hidden border border-[#0a192f] bg-[#112240]">
                            <img src="<?= $photoHeader ?>" 
                                 alt="Profile"
                                 referrerpolicy="no-referrer"
                                 onerror="this.src='<?= $defaultAvatar ?>'"
                                 class="w-full h-full object-cover">
                        </div>
                    </div>
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border border-[#0a192f] rounded-full shadow-sm"></span>
                </div>
            </div>

        <?php else: ?>
            <a href="/mahayana/auth_user/login.php" class="btn-restricted group flex items-center gap-2 px-3 py-1 rounded-md hover:bg-[#D4AF37]">
                <span class="text-[8px] font-bold text-white group-hover:text-[#0a192f] uppercase tracking-tight transition-colors">Sign In</span>
                <i class="fa-solid fa-lock text-[#D4AF37] group-hover:text-[#0a192f] text-[8px]"></i>
            </a>
        <?php endif; ?>
    </div>
</header>