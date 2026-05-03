<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../config/koneksi.php';

if (!$conn) {
    die("Koneksi database gagal");
}

date_default_timezone_set('Asia/Jakarta');

// ===================== HANDLE POST =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = mysqli_real_escape_string($conn, $_POST['title']);
    $type       = mysqli_real_escape_string($conn, $_POST['type']);
    $tipe_promo = mysqli_real_escape_string($conn, $_POST['tipe_promo']);
    $points     = (int)$_POST['points'];
    $is_active  = isset($_POST['is_active']) ? 1 : 0;
    $created_at = date('Y-m-d H:i:s');

    // ===================== VALIDASI IMAGE =====================
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        die("<script>alert('Gambar wajib diupload!'); window.history.back();</script>");
    }

    $image = $_FILES['image']['name'];
    $tmp   = $_FILES['image']['tmp_name'];
    $ext   = strtolower(pathinfo($image, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        die("<script>alert('Format gambar tidak valid! Gunakan JPG, PNG, atau WEBP.'); window.history.back();</script>");
    }

    $dir = "../../uploads/promo/";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $newName = "promo_" . time() . "_" . rand(100, 999) . "." . $ext;
    $path = $dir . $newName;

    if (move_uploaded_file($tmp, $path)) {
        // ===================== INSERT =====================
        $sql = "INSERT INTO promos (title, type, tipe_promo, points, image, is_active, created_at)
                VALUES ('$title', '$type', '$tipe_promo', '$points', '$newName', '$is_active', '$created_at')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>
                alert('Promo berhasil disimpan!');
                window.location.href='index.php';
            </script>";
            exit;
        } else {
            die("DB ERROR: " . mysqli_error($conn));
        }
    } else {
        die("Upload gambar gagal");
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Promo - Mahayana</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        @media (min-width: 768px) {
            .sidebar-fixed { width: 18rem; position: fixed; left: 0; top: 0; height: 100vh; }
            .content-wrapper { margin-left: 18rem; width: calc(100% - 18rem); }
        }
    </style>
</head>
<body class="antialiased text-slate-900">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="sidebar-fixed z-50 bg-white">
            <?php include '../components/sidebar.php'; ?>
        </aside>

        <!-- Main Content -->
        <div class="content-wrapper flex flex-col flex-1 min-w-0">
            <main class="p-4 md:p-10">
                <div class="max-w-4xl mx-auto">
                    
                    <!-- Header -->
                    <div class="flex items-center gap-4 mb-8">
                        <a href="index.php" class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">
                            <i class="fa-solid fa-arrow-left text-sm"></i>
                        </a>
                        <div>
                            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Tambah Promo Baru</h1>
                            <p class="text-slate-500 text-sm">Lengkapi formulir di bawah untuk membuat konten promosi.</p>
                        </div>
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <!-- Left Column: Form Fields -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm space-y-5">
                                
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Judul Promo</label>
                                    <input type="text" name="title" placeholder="Contoh: Mudik Asik Bareng Mahayana" required 
                                        class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl focus:ring-2 focus:ring-yellow-400 focus:bg-white outline-none transition-all text-sm font-semibold">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Label (Type)</label>
                                        <input type="text" name="type" placeholder="Diskon / Cashback" required 
                                            class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl focus:ring-2 focus:ring-yellow-400 focus:bg-white outline-none transition-all text-sm font-semibold">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Kategori</label>
                                        <select name="tipe_promo" required 
                                            class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl focus:ring-2 focus:ring-yellow-400 focus:bg-white outline-none transition-all text-sm font-bold">
                                            <option value="">Pilih Kategori</option>
                                            <option value="Shuttle">Shuttle</option>
                                            <option value="Wisata">Wisata</option>
                                            <option value="Sewa Armada">Sewa Armada</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Potongan Harga / Poin (%)</label>
                                    <div class="relative">
                                        <input type="number" name="points" placeholder="0" required 
                                            class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl focus:ring-2 focus:ring-yellow-400 focus:bg-white outline-none transition-all text-sm font-bold">
                                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">%</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                    <input type="checkbox" name="is_active" id="is_active" checked class="w-5 h-5 accent-slate-900">
                                    <label for="is_active" class="text-sm font-bold text-slate-700 cursor-pointer">Terbitkan Sekarang (Aktif)</label>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-slate-900 hover:bg-black text-yellow-400 py-5 rounded-[2rem] font-black text-sm shadow-xl transition-all flex items-center justify-center gap-3">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                SIMPAN DATA PROMO
                            </button>
                        </div>

                        <!-- Right Column: Image Upload -->
                        <div class="lg:col-span-1">
                            <div class="bg-white p-6 rounded-[2.5rem] border border-slate-200 shadow-sm sticky top-24">
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-4">Banner Preview</label>
                                
                                <div id="preview-container" class="relative group aspect-[4/3] bg-slate-100 rounded-3xl overflow-hidden border-2 border-dashed border-slate-200 flex items-center justify-center">
                                    <img id="image-preview" src="#" class="hidden w-full h-full object-cover">
                                    <div id="placeholder-text" class="text-center p-6 transition-opacity">
                                        <i class="fa-solid fa-image text-slate-300 text-4xl mb-3"></i>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Belum ada gambar</p>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <input type="file" name="image" id="image-input" accept="image/*" required class="hidden">
                                    <label for="image-input" class="w-full flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-600 py-3 rounded-xl font-bold text-xs cursor-pointer transition-all">
                                        <i class="fa-solid fa-camera"></i>
                                        PILIH GAMBAR
                                    </label>
                                    <p class="text-[10px] text-center text-slate-400 mt-3 italic font-medium">Rekomendasi: 1200 x 800px (Max 2MB)</p>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Preview Image Script
        const imageInput = document.getElementById('image-input');
        const imagePreview = document.getElementById('image-preview');
        const placeholderText = document.getElementById('placeholder-text');
        const previewContainer = document.getElementById('preview-container');

        imageInput.onchange = evt => {
            const [file] = imageInput.files;
            if (file) {
                imagePreview.src = URL.createObjectURL(file);
                imagePreview.classList.remove('hidden');
                placeholderText.classList.add('hidden');
                previewContainer.classList.remove('border-dashed');
                previewContainer.classList.add('border-solid', 'border-slate-100');
            }
        }
    </script>
</body>
</html>