<?php
require_once '../../config/koneksi.php';

// PROSES SIMPAN
if(isset($_POST['submit'])){
    $asal   = mysqli_real_escape_string($conn, $_POST['departure_city']);
    $tujuan = mysqli_real_escape_string($conn, $_POST['arrival_city']);
    $harga  = (int) $_POST['base_price'];

    if(empty($asal) || empty($tujuan) || empty($harga)){
        $error = "Semua field wajib diisi";
    } else {
        mysqli_query($conn, "INSERT INTO routes (departure_city, arrival_city, base_price)
        VALUES ('$asal','$tujuan','$harga')");

        header("Location: index.php?success=create");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Rute</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

<?php include '../components/sidebar.php'; ?>

<div class="flex-1 flex flex-col">

    <!-- HEADER -->
    <div class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <h1 class="text-lg font-bold">Tambah Rute</h1>
        <a href="index.php" class="bg-gray-200 px-4 py-2 rounded-lg">← Kembali</a>
    </div>

    <!-- CONTENT -->
    <div class="p-6">
        <div class="max-w-xl mx-auto bg-white p-6 rounded-2xl shadow">

            <h2 class="text-xl font-bold mb-4">Form Rute</h2>

            <?php if(isset($error)): ?>
                <div class="bg-red-100 text-red-600 p-3 mb-4 rounded"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">

                <input type="text" name="departure_city" placeholder="Kota Asal"
                class="w-full border px-3 py-2 rounded-lg">

                <input type="text" name="arrival_city" placeholder="Kota Tujuan"
                class="w-full border px-3 py-2 rounded-lg">

                <input type="number" name="base_price" placeholder="Harga"
                class="w-full border px-3 py-2 rounded-lg">

                <div class="flex justify-end gap-2">
                    <a href="index.php" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</a>

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