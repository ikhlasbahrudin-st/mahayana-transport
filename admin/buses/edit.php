<?php
require_once '../../config/koneksi.php';

// VALIDASI ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die("ID tidak valid");
}

$id = (int) $_GET['id'];

// AMBIL DATA
$result = mysqli_query($conn, "SELECT * FROM buses WHERE id=$id");

if(mysqli_num_rows($result) == 0){
    die("Data tidak ditemukan");
}

$data = mysqli_fetch_assoc($result);

// PROSES UPDATE
if(isset($_POST['submit'])){
    $bus = mysqli_real_escape_string($conn, $_POST['bus_name']);
    $plat = mysqli_real_escape_string($conn, $_POST['plate_number']);
    $kapasitas = (int) $_POST['capacity'];

    $imageName = $data['image']; // default pakai lama

    // CEK UPLOAD GAMBAR BARU
    if(isset($_FILES['image']) && $_FILES['image']['name'] != ''){
        $file = $_FILES['image'];

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if(!in_array($ext, $allowed)){
            $error = "Format gambar harus JPG, PNG, atau WEBP";
        } else {

            $newName = 'bus_'.time().'.'.$ext;
            $target = '../../assets/bus/'.$newName;

            if(move_uploaded_file($file['tmp_name'], $target)){

                // HAPUS GAMBAR LAMA (kalau ada)
                if(!empty($data['image']) && file_exists('../../assets/bus/'.$data['image'])){
                    unlink('../../assets/bus/'.$data['image']);
                }

                $imageName = $newName;

            } else {
                $error = "Gagal upload gambar";
            }
        }
    }

    // VALIDASI
    if(empty($bus) || empty($plat) || empty($kapasitas)){
        $error = "Semua field wajib diisi";
    }

    // UPDATE
    if(!isset($error)){
        mysqli_query($conn, "
            UPDATE buses SET 
                bus_name='$bus',
                plate_number='$plat',
                capacity='$kapasitas',
                image='$imageName'
            WHERE id=$id
        ");

        header("Location: index.php?success=update");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Bus</title>
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
            <h1 class="text-lg font-bold text-gray-800">Edit Data Bus</h1>

            <a href="index.php"
               class="text-sm bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg">
               ← Kembali
            </a>
        </div>

        <!-- MAIN -->
        <div class="p-6">

            <div class="max-w-xl mx-auto bg-white p-6 rounded-2xl shadow">

                <h2 class="text-xl font-bold mb-4 text-gray-800">
                    Form Edit Bus
                </h2>

                <?php if(isset($error)): ?>
                <div class="bg-red-100 text-red-600 p-3 mb-4 rounded">
                    <?= $error ?>
                </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-4">

                    <!-- NAMA -->
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Nama Bus</label>
                        <input type="text" name="bus_name"
                            value="<?= $data['bus_name'] ?>"
                            class="w-full mt-1 border px-3 py-2 rounded-lg">
                    </div>

                    <!-- PLAT -->
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Plat Nomor</label>
                        <input type="text" name="plate_number"
                            value="<?= $data['plate_number'] ?>"
                            class="w-full mt-1 border px-3 py-2 rounded-lg">
                    </div>

                    <!-- KAPASITAS -->
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Kapasitas</label>
                        <input type="number" name="capacity"
                            value="<?= $data['capacity'] ?>"
                            class="w-full mt-1 border px-3 py-2 rounded-lg">
                    </div>

                    <!-- PREVIEW GAMBAR -->
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Gambar Saat Ini</label>
                        <div class="mt-2">
                            <?php if(!empty($data['image'])): ?>
                                <img src="../../assets/bus/<?= $data['image'] ?>"
                                     class="w-full h-40 object-cover rounded-lg border">
                            <?php else: ?>
                                <div class="w-full h-40 bg-gray-200 flex items-center justify-center rounded-lg">
                                    <span class="text-gray-400 text-sm">Tidak ada gambar</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- UPLOAD BARU -->
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Ganti Gambar</label>
                        <input type="file" name="image"
                            class="w-full mt-1 border px-3 py-2 rounded-lg">
                        <p class="text-xs text-gray-400 mt-1">
                            Kosongkan jika tidak ingin mengganti gambar
                        </p>
                    </div>

                    <!-- BUTTON -->
                    <div class="flex justify-end gap-2 pt-4">
                        <a href="index.php"
                           class="px-4 py-2 bg-gray-200 rounded-lg">
                           Batal
                        </a>

                        <button name="submit"
                            class="px-5 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold">
                            Update
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