<?php
require_once '../../config/koneksi.php';

// PROSES SIMPAN
if(isset($_POST['submit'])){
    $bus = mysqli_real_escape_string($conn, $_POST['bus_name']);
    $plat = mysqli_real_escape_string($conn, $_POST['plate_number']);
    $kapasitas = (int) $_POST['capacity'];

    // HANDLE UPLOAD GAMBAR
    $imageName = '';

    if(isset($_FILES['image']) && $_FILES['image']['name'] != ''){
        $file = $_FILES['image'];

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if(!in_array($ext, $allowed)){
            $error = "Format gambar harus JPG, PNG, atau WEBP";
        } else {

            $imageName = 'bus_'.time().'.'.$ext;
            $target = '../../assets/bus/'.$imageName;

            if(!move_uploaded_file($file['tmp_name'], $target)){
                $error = "Gagal upload gambar";
            }
        }
    }

    // VALIDASI
    if(empty($bus) || empty($plat) || empty($kapasitas)){
        $error = "Semua field wajib diisi";
    }

    // SIMPAN
    if(!isset($error)){
        mysqli_query($conn, "
            INSERT INTO buses (bus_name, plate_number, capacity, image)
            VALUES ('$bus','$plat','$kapasitas','$imageName')
        ");

        header("Location: index.php?success=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Bus</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <?php include '../components/sidebar.php'; ?>

    <!-- CONTENT -->
    <div class="flex-1 flex flex-col">

        <!-- HEADER -->
        <div class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-gray-800">Tambah Bus</h1>

            <a href="index.php"
               class="text-sm bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg">
               ← Kembali
            </a>
        </div>

        <!-- MAIN -->
        <div class="p-6">

            <div class="max-w-xl mx-auto bg-white p-6 rounded-2xl shadow">

                <h2 class="text-xl font-bold mb-4">Form Bus</h2>

                <?php if(isset($error)): ?>
                <div class="bg-red-100 text-red-600 p-3 mb-4 rounded">
                    <?= $error ?>
                </div>
                <?php endif; ?>

                <!-- FORM -->
                <form method="POST" enctype="multipart/form-data" class="space-y-4">

                    <!-- NAMA -->
                    <input type="text" name="bus_name" placeholder="Nama Bus"
                        class="w-full border px-3 py-2 rounded-lg">

                    <!-- PLAT -->
                    <input type="text" name="plate_number" placeholder="Plat Nomor"
                        class="w-full border px-3 py-2 rounded-lg">

                    <!-- KAPASITAS -->
                    <input type="number" name="capacity" placeholder="Kapasitas"
                        class="w-full border px-3 py-2 rounded-lg">

                    <!-- UPLOAD GAMBAR -->
                    <div>
                        <label class="text-sm font-semibold">Gambar Bus</label>
                        <input type="file" name="image"
                            class="w-full border px-3 py-2 rounded-lg mt-1">
                    </div>

                    <!-- BUTTON -->
                    <div class="flex justify-end gap-2">
                        <a href="index.php"
                           class="px-4 py-2 bg-gray-200 rounded-lg">
                           Batal
                        </a>

                        <button name="submit"
                            class="px-4 py-2 bg-yellow-400 rounded-lg font-bold">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="bg-white border-t text-center text-xs text-gray-400 py-3">
            © <?= date('Y') ?> Mahayana Admin
        </div>

    </div>

</div>

</body>
</html>