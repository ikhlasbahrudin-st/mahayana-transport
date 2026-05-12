<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
?>

<nav class="bg-[#020617] border-b border-yellow-500/10 shadow">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">

        <!-- LEFT (Logo + space for hamburger) -->
        <div class="flex items-center gap-3">

            <!-- Spacer biar tidak ketabrak hamburger -->
            <div class="w-8 md:hidden"></div>

      
        </div>

        <!-- RIGHT -->
        <div class="flex items-center gap-2 sm:gap-4">

            <!-- Nama Admin -->
            <span class="text-gray-300 text-xs sm:text-sm truncate max-w-[100px] sm:max-w-none">
                <?= $_SESSION['admin']['fullname'] ?? 'Admin' ?>
            </span>

            <!-- Logout -->
            <a href="../auth_admin/logout.php"
               class="bg-yellow-400 text-black px-2 sm:px-3 py-1 rounded-lg text-xs sm:text-sm hover:bg-yellow-300 transition">
               Logout
            </a>

        </div>

    </div>

</nav>