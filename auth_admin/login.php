<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#0f172a',
                        gold: '#facc15'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gradient-to-br from-navy to-black min-h-screen flex items-center justify-center">

<div class="bg-navy border border-yellow-500/20 shadow-2xl rounded-2xl p-8 w-full max-w-md">

    <h2 class="text-2xl font-bold text-center text-gold mb-6">
        Admin Login
    </h2>

    <?php if(isset($_GET['error'])) { ?>
        <div class="bg-red-900 text-red-200 text-sm p-3 rounded-lg mb-4 text-center">
            Email atau Password salah!
        </div>
    <?php } ?>

    <form method="POST" action="proses_login.php" class="space-y-4">

        <input type="email" name="email" placeholder="Email Admin"
            class="w-full px-4 py-3 rounded-lg bg-black/50 border border-gray-700 text-white focus:outline-none focus:border-gold"
            required>

        <input type="password" name="password" placeholder="Password"
            class="w-full px-4 py-3 rounded-lg bg-black/50 border border-gray-700 text-white focus:outline-none focus:border-gold"
            required>

        <button type="submit"
            class="w-full bg-gold text-black font-semibold py-3 rounded-lg hover:bg-yellow-400 transition">
            Login
        </button>
    </form>

</div>

</body>
</html>