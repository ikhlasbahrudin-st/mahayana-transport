<?php
include '../../config/koneksi.php';

$bus_id = (int)$_GET['bus_id'];

$bus = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM buses WHERE id=$bus_id"));
$capacity = (int)$bus['capacity'];

// Hapus kursi lama agar tidak duplikat
mysqli_query($conn, "DELETE FROM seats WHERE bus_id=$bus_id");

/**
 * MAPPING LAYOUT HIACE PREMIO 14 SEAT (Sesuai Gambar)
 * Baris A: Sopir (Kiri), Dashboard/Pintu depan, Kursi 1 (Kanan)
 * Baris B: 4, 3, 2 (Kiri), Pintu (Kanan)
 * Baris C: 7, 6 (Kiri), Lorong, 5 (Kanan)
 * Baris D: 10, 9 (Kiri), Lorong, 8 (Kanan)
 * Baris E: 14, 13, 12, 11 (Full Belakang)
 */

// 1. INSERT SOPIR (Posisi kolom 1)
mysqli_query($conn, "
    INSERT INTO seats (bus_id, seat_number, row_label, col_number, position, is_driver)
    VALUES ($bus_id, 'SOPIR', 'A', 1, 'front', 1)
");

// 2. DAFTAR KURSI PENUMPANG (Sesuai urutan visual gambar)
// Format: ['nomor_kursi', 'row', 'col', 'position']
$layout = [
    ['1',  'A', 4, 'front'],  // Depan samping pintu
    
    ['4',  'B', 1, 'middle'], // Baris 2 kiri
    ['3',  'B', 2, 'middle'], // Baris 2 tengah
    ['2',  'B', 3, 'middle'], // Baris 2 kanan lorong
    
    ['7',  'C', 1, 'middle'], // Baris 3 kiri
    ['6',  'C', 2, 'middle'], // Baris 3 tengah
    ['5',  'C', 4, 'middle'], // Baris 3 kanan sendirian
    
    ['10', 'D', 1, 'middle'], // Baris 4 kiri
    ['9',  'D', 2, 'middle'], // Baris 4 tengah
    ['8',  'D', 4, 'middle'], // Baris 4 kanan sendirian
    
    ['14', 'E', 1, 'back'],   // Belakang pojok kiri
    ['13', 'E', 2, 'back'],
    ['12', 'E', 3, 'back'],
    ['11', 'E', 4, 'back'],   // Belakang pojok kanan
];

$inserted_count = 0;

foreach ($layout as $s) {
    // Cek agar tidak melebihi kapasitas bus di database
    if ($inserted_count < $capacity) {
        $seat_num = $s[0];
        $row      = $s[1];
        $col      = $s[2];
        $pos      = $s[3];

        mysqli_query($conn, "
            INSERT INTO seats (bus_id, seat_number, row_label, col_number, position)
            VALUES ($bus_id, '$seat_num', '$row', $col, '$pos')
        ");
        $inserted_count++;
    }
}

header("Location: index.php?bus_id=$bus_id");
exit;