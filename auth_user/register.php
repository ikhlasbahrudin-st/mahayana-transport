<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">    <title>Register - Mahayana Shuttle</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-6">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
        <div class="bg-red-600 p-8 text-white text-center">
            <h1 class="text-2xl font-black italic tracking-tighter uppercase">Mahayana</h1>
            <p class="text-[10px] font-bold opacity-80 uppercase tracking-[0.2em] mt-1">Daftar Akun Baru</p>
        </div>

        <div class="p-8">
            <form action="proses_register.php" method="POST" class="space-y-5">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block mb-1">Nama Lengkap</label>
                    <div class="flex items-center gap-3 border-b-2 border-gray-100 pb-2 focus-within:border-red-500 transition-all">
                        <i class="fa-solid fa-user text-gray-300"></i>
                        <input type="text" name="fullname" required placeholder="Nama Anda" class="w-full focus:outline-none text-sm font-bold text-gray-700 bg-transparent">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block mb-1">Email</label>
                    <div class="flex items-center gap-3 border-b-2 border-gray-100 pb-2 focus-within:border-red-500 transition-all">
                        <i class="fa-solid fa-envelope text-gray-300"></i>
                        <input type="email" name="email" required placeholder="email@domain.com" class="w-full focus:outline-none text-sm font-bold text-gray-700 bg-transparent">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block mb-1">Password</label>
                    <div class="flex items-center gap-3 border-b-2 border-gray-100 pb-2 focus-within:border-red-500 transition-all">
                        <i class="fa-solid fa-lock text-gray-300"></i>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full focus:outline-none text-sm font-bold text-gray-700 bg-transparent">
                    </div>
                </div>

                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-black py-4 rounded-xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-xs mt-4">
                    Daftar
                </button>
            </form>

            <div class="relative py-6 flex items-center">
                <div class="flex-grow border-t border-gray-100"></div>
                <span class="flex-shrink mx-4 text-[10px] font-bold text-gray-400 uppercase">Atau</span>
                <div class="flex-grow border-t border-gray-100"></div>
            </div>

            <div class="flex justify-center">
                <div id="g_id_onload"
                     data-client_id="286580353564-o1p2biqoiv5gfb4fiionvorc9jbl2lge.apps.googleusercontent.com"
                     data-context="signup"
                     data-ux_mode="popup"
                     data-callback="handleCredentialResponse"
                     data-auto_prompt="false">
                </div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-shape="pill"
                     data-theme="outline"
                     data-text="signup_with"
                     data-size="large"
                     data-logo_alignment="left"
                     data-width="320">
                </div>
            </div>

            <p class="text-center text-[11px] text-gray-500 font-medium mt-8">
                Sudah punya akun? <a href="login.php" class="text-red-600 font-black">Login</a>
            </p>
        </div>
    </div>

    <form id="google-form" action="proses_register.php" method="POST" style="display:none;">
        <input type="hidden" name="id_token" id="google_token">
    </form>

    <script>
        function handleCredentialResponse(response) {
            // Masukkan token JWT dari Google ke input hidden
            document.getElementById('google_token').value = response.credential;
            
            // Submit form secara otomatis ke proses_register.php
            document.getElementById('google-form').submit();
        }
    </script>
</body>
</html>