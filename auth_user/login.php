<?php
session_start();
// Jika sudah login, tendang balik ke index
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">    <title>Login - Mahayana Shuttle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
        <div class="bg-[#003366] p-10 text-white text-center relative overflow-hidden">
            <i class="fa-solid fa-bus absolute -right-4 -bottom-4 text-white/10 text-8xl rotate-12"></i>
            <h1 class="text-2xl font-black italic tracking-tighter uppercase text-red-500 relative z-10">Mahayana</h1>
            <p class="text-[10px] font-bold opacity-80 uppercase tracking-[0.2em] mt-1 relative z-10">Selamat Datang Kembali</p>
        </div>

        <div class="p-8">
            <form action="proses_login.php" method="POST" class="space-y-6">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block mb-1">Email</label>
                    <div class="flex items-center gap-3 border-b-2 border-gray-100 pb-2 focus-within:border-red-500 transition-all">
                        <i class="fa-solid fa-at text-gray-300 text-sm"></i>
                        <input type="email" name="email" required placeholder="admin@gmail.com" 
                               class="w-full focus:outline-none text-sm font-bold text-gray-700 bg-transparent">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block mb-1">Password</label>
                    <div class="flex items-center gap-3 border-b-2 border-gray-100 pb-2 focus-within:border-red-500 transition-all">
                        <i class="fa-solid fa-key text-gray-300 text-sm"></i>
                        <input type="password" name="password" required placeholder="••••••••" 
                               class="w-full focus:outline-none text-sm font-bold text-gray-700 bg-transparent">
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-black py-4 rounded-xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-xs">
                    Masuk Sekarang
                </button>
            </form>

            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-gray-100"></span></div>
                <div class="relative flex justify-center text-[10px] uppercase">
                    <span class="bg-white px-3 text-gray-400 font-black tracking-widest">Atau</span>
                </div>
            </div>

            <div class="flex justify-center">
                <div id="g_id_onload"
                     data-client_id="286580353564-o1p2biqoiv5gfb4fiionvorc9jbl2lge.apps.googleusercontent.com"
                     data-context="signin"
                     data-ux_mode="popup"
                     data-callback="handleCredentialResponse"
                     data-auto_prompt="false">
                </div>

                <div class="g_id_signin"
                     data-type="standard"
                     data-shape="pill"
                     data-theme="outline"
                     data-text="signin_with"
                     data-size="large"
                     data-logo_alignment="left"
                     data-width="320">
                </div>
            </div>

            <p class="text-center text-[11px] text-gray-500 font-medium mt-8">
                Belum punya akun? <a href="register.php" class="text-[#003366] font-black border-b border-blue-100">Daftar Sekarang</a>
            </p>
        </div>
    </div>

    <form id="google-login-form" action="proses_register.php" method="POST" style="display:none;">
        <input type="hidden" name="id_token" id="google_token">
    </form>

    <script>
        function handleCredentialResponse(response) {
            document.getElementById('google_token').value = response.credential;
            document.getElementById('google-login-form').submit();
        }
    </script>
</body>
</html>