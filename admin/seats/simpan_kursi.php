<?php
include '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

/* =========================
   AMBIL DATA
========================= */
$bus_id = (int)($_POST['bus_id'] ?? 0); 
$schedule_id = (int)($_POST['schedule_id'] ?? 0);
$travel_date = $_POST['travel_date'] ?? null; // future use (tidak dipakai di sini)
$seats_input = $_POST['seats'] ?? []; 

if (!$schedule_id || !$bus_id) {
    echo "<script>alert('Data Bus atau Jadwal tidak lengkap!'); history.back();</script>";
    exit;
}

/* =========================
   TRANSACTION
========================= */
mysqli_begin_transaction($conn);

try {

    /* =========================
       HAPUS HANYA TEMPLATE
       ❗ JANGAN HAPUS DATA HARIAN
    ========================= */
    $delete = mysqli_query($conn, "
        DELETE FROM seats 
        WHERE schedule_id = $schedule_id
        AND travel_date IS NULL
    ");

    if (!$delete) {
        throw new Exception("Gagal membersihkan template: " . mysqli_error($conn));
    }

    /* =========================
       INSERT TEMPLATE BARU
    ========================= */
    foreach ($seats_input as $row_label => $cols) {
        foreach ($cols as $col_number => $seat_num) {
            
            $seat_num = strtoupper(trim($seat_num));

            if ($seat_num === '') continue;

            /* SANITASI */
            $clean_row = mysqli_real_escape_string($conn, $row_label);
            $clean_col = (int)$col_number;
            $clean_seat_num = mysqli_real_escape_string($conn, $seat_num);

            /* POSISI */
            if ($clean_row === 'A') {
                $pos = 'front';
            } elseif (in_array($clean_row, ['E', 'F', 'G', 'H'])) {
                $pos = 'back';
            } else {
                $pos = 'middle';
            }

            /* DRIVER */
            $is_driver = ($clean_seat_num === 'DRIVER' || $clean_seat_num === 'DRV') ? 1 : 0;

            /* INSERT TEMPLATE */
            $query = "
                INSERT INTO seats (
                    bus_id,
                    schedule_id, 
                    seat_number, 
                    row_label, 
                    col_number, 
                    position, 
                    is_driver, 
                    status,
                    travel_date,
                    created_at
                ) VALUES (
                    $bus_id,
                    $schedule_id, 
                    '$clean_seat_num', 
                    '$clean_row', 
                    $clean_col, 
                    '$pos', 
                    $is_driver, 
                    'available',
                    NULL, -- 🔥 TEMPLATE WAJIB NULL
                    NOW()
                )
            ";

            if (!mysqli_query($conn, $query)) {
                throw new Exception("Gagal menyimpan kursi $clean_seat_num: " . mysqli_error($conn));
            }
        }
    }

    mysqli_commit($conn);

    echo "<script>
        alert('Layout kursi berhasil disimpan (template aman)!');
        window.location='index.php?bus_id=$bus_id&schedule_id=$schedule_id';
    </script>";

} catch (Exception $e) {

    mysqli_rollback($conn);

    echo "<script>
        alert('Gagal menyimpan: " . addslashes($e->getMessage()) . "');
        window.history.back();
    </script>";
}

exit;