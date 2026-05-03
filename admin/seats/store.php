<?php
include '../../config/koneksi.php';

// Pastikan request menggunakan POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dan pastikan tipe datanya benar
    $bus_id = (int)$_POST['bus_id'];
    $schedule_id = isset($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;
    $seat_number = strtoupper(trim($_POST['seat_number']));

    if (!$bus_id || !$schedule_id) {
        echo "<script>alert('Bus atau Jadwal tidak terpilih!'); history.back();</script>";
        exit;
    }

    // 1. CEK DUPLIKAT (Sekarang dicek per Jadwal agar tidak bentrok)
    $cek = mysqli_query($conn, "SELECT id FROM seats WHERE schedule_id=$schedule_id AND seat_number='$seat_number'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Nomor kursi $seat_number sudah ada di jadwal ini!'); history.back();</script>";
        exit;
    }

    // 2. MAPPING OTOMATIS (Hiace Premio 14 Seat)
    $row_label = 'Z'; 
    $col_number = 1;
    $position = 'middle';
    $is_driver = 0;

    $layout_map = [
        'SOPIR' => ['A', 1, 'front', 1],
        'DRV'   => ['A', 1, 'front', 1], // Tambahan alias untuk driver
        '1'     => ['A', 4, 'front', 0],
        '4'     => ['B', 1, 'middle', 0],
        '3'     => ['B', 2, 'middle', 0],
        '2'     => ['B', 3, 'middle', 0],
        '7'     => ['C', 1, 'middle', 0],
        '6'     => ['C', 2, 'middle', 0],
        '5'     => ['C', 4, 'middle', 0],
        '10'    => ['D', 1, 'middle', 0],
        '9'     => ['D', 2, 'middle', 0],
        '8'     => ['D', 4, 'middle', 0],
        '14'    => ['E', 1, 'back', 0],
        '13'    => ['E', 2, 'back', 0],
        '12'    => ['E', 3, 'back', 0],
        '11'    => ['E', 4, 'back', 0],
    ];

    if (array_key_exists($seat_number, $layout_map)) {
        $row_label  = $layout_map[$seat_number][0];
        $col_number = $layout_map[$seat_number][1];
        $position   = $layout_map[$seat_number][2];
        $is_driver  = $layout_map[$seat_number][3];
    }

    // 3. INSERT KE DATABASE (Menambahkan schedule_id dan status default)
    $query = "INSERT INTO seats (bus_id, schedule_id, seat_number, row_label, col_number, position, is_driver, status) 
              VALUES ($bus_id, $schedule_id, '$seat_number', '$row_label', $col_number, '$position', $is_driver, 'available')";

    if (mysqli_query($conn, $query)) {
        // Redirect kembali ke halaman layout dengan parameter lengkap
        header("Location: index.php?bus_id=$bus_id&schedule_id=$schedule_id&status=success");
    } else {
        echo "Error SQL: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
}
exit;