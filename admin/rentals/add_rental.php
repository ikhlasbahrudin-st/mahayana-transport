<?php
include '../../config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Sewa Armada - Mahayana</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
.input{
    width:100%;
    padding:12px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    outline:none;
    transition:.2s;
    background:white;
}
.input:focus{
    border-color:#facc15;
    box-shadow:0 0 0 3px rgba(250,204,21,0.2);
}
</style>
</head>

<body class="bg-slate-50 flex h-screen overflow-hidden">

<!-- SIDEBAR -->
<div class="w-64 hidden md:block border-r bg-slate-950">
    <?php include '../components/sidebar.php'; ?>
</div>

<div class="flex-1 flex flex-col overflow-hidden">

<?php include '../components/navbar.php'; ?>

<main class="flex-1 overflow-y-auto p-6">

<div class="max-w-4xl mx-auto">

<!-- HEADER -->
<div class="flex items-center gap-4 mb-8">
    <a href="index.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-2xl font-bold">Tambah Sewa Baru</h1>
        <p class="text-sm text-gray-500">Cover + Dynamic Gallery Upload</p>
    </div>
</div>

<!-- FORM -->
<div class="bg-white rounded-2xl shadow border">

<form action="proses_rental.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">

<!-- CUSTOMER -->
<div class="grid md:grid-cols-2 gap-6">
    <input type="text" name="customer_name" placeholder="Nama Penyewa" required class="input">
    <input type="text" name="customer_phone" placeholder="No HP" required class="input">
</div>

<!-- BUS -->
<div class="grid md:grid-cols-2 gap-6">
    <input type="text" name="bus_name" placeholder="Nama Bus" required class="input">
    <input type="number" name="capacity" placeholder="Kapasitas Kursi" required class="input">
</div>

<!-- HARGA -->
<div class="grid md:grid-cols-2 gap-6">
    <input type="number" name="price_per_day" id="price" placeholder="Harga / Hari" required class="input">
    <input type="number" id="days" placeholder="Jumlah Hari" required class="input">
</div>

<!-- TOTAL -->
<div>
    <label class="text-xs font-bold text-gray-400">Total Harga</label>
    <input type="number" name="total_price" id="total" readonly class="input bg-gray-100 font-bold">
</div>

<!-- DATE -->
<input type="date" name="rental_date" required class="input">

<!-- DESCRIPTION -->
<textarea name="description" placeholder="Deskripsi perjalanan" class="input"></textarea>

<!-- COVER IMAGE -->
<div>
    <label class="text-xs font-bold text-gray-400">Cover Image (Utama)</label>
    <input type="file" name="image" accept="image/*" required class="input">
</div>

<!-- ================= GALLERY DYNAMIC ================= -->
<div>
    <div class="flex justify-between items-center mb-2">
        <label class="text-xs font-bold text-gray-400">Gallery Bus (Bisa Banyak)</label>

        <button type="button" onclick="addImageInput()"
            class="bg-yellow-400 px-3 py-1 rounded-lg text-xs font-bold">
            + Tambah Gambar
        </button>
    </div>

    <div id="galleryBox" class="space-y-2"></div>
</div>

<!-- STATUS -->
<select name="status" class="input">
    <option value="pending">Pending</option>
    <option value="confirmed">Confirmed</option>
</select>

<!-- BUTTON -->
<div class="flex gap-3">
    <button class="bg-yellow-400 px-6 py-3 rounded-xl font-bold">
        Simpan Data
    </button>
    <a href="index.php" class="bg-gray-200 px-6 py-3 rounded-xl">
        Batal
    </a>
</div>

</form>

</div>
</div>

</main>
</div>

<script>
// ================= AUTO TOTAL =================
const price = document.getElementById('price');
const days = document.getElementById('days');
const total = document.getElementById('total');

function hitung(){
    total.value = (price.value || 0) * (days.value || 0);
}
price.addEventListener('input', hitung);
days.addEventListener('input', hitung);

// ================= ADD IMAGE INPUT =================
function addImageInput(){
    const box = document.getElementById('galleryBox');

    const div = document.createElement('div');
    div.className = "flex items-center gap-2";

    div.innerHTML = `
        <input type="file" name="gallery[]" accept="image/*"
               class="w-full border p-2 rounded-xl">

        <button type="button"
            onclick="this.parentElement.remove()"
            class="bg-red-500 text-white px-3 py-2 rounded-lg">
            <i class="fa fa-x"></i>
        </button>
    `;

    box.appendChild(div);
}
</script>

</body>
</html>