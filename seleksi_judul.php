<?php
session_start();
include('koneksi.php');

// Pastikan hanya prodi yang bisa mengakses halaman ini
if ($_SESSION['role'] != 'prodi') {
    header('Location: login.php');
    exit();
}

// Query untuk mengambil data pengajuan judul yang statusnya 'menunggu'
$query = "SELECT * FROM pengajuan_judul WHERE status='menunggu'";
$result = mysqli_query($conn, $query);

// Proses saat tombol 'terima' atau 'tolak' diklik
if (isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['update_status'] == 'terima' ? 'diterima' : 'ditolak';
    $alasan = $_POST['alasan'] ?? '';

    // Perbarui status pengajuan judul
    $update_query = "UPDATE pengajuan_judul SET status='$status', alasan='$alasan' WHERE id='$id'";
    if (mysqli_query($conn, $update_query)) {
        echo "Status pengajuan berhasil diperbarui.";
    } else {
        echo "Terjadi kesalahan saat memperbarui status.";
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleksi Judul</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Seleksi Judul</h1>

    <!-- Menampilkan pengajuan judul yang masih menunggu -->
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <form method="POST" action="">
            <h3><?= htmlspecialchars($row['judul']) ?></h3>
            <p><?= htmlspecialchars($row['abstrak']) ?></p>
            
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            
            <!-- Menambahkan textarea untuk alasan jika statusnya 'menunggu' dan ingin ditolak -->
            <?php if ($row['status'] == 'menunggu'): ?>
                <textarea name="alasan" placeholder="Alasan ditolak (jika ada)"></textarea>
            <?php endif; ?>

            <!-- Tombol untuk menerima atau menolak pengajuan -->
            <button type="submit" name="update_status" value="terima">
                Terima
            </button>
            <button type="submit" name="update_status" value="tolak">
                Tolak
            </button>
        </form>
        <hr>
    <?php endwhile; ?>
</body>
</html>
