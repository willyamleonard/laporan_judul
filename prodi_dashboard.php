<?php
session_start();

// Pastikan hanya prodi yang bisa mengakses halaman ini
if ($_SESSION['role'] != 'prodi') {
    header('Location: login.php');
    exit();
}

// Menangani logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

include('koneksi.php');

// Menangani seleksi judul
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['seleksi'])) {
    $id = $_POST['id'];
    $status = $_POST['status']; // diterima / ditolak
    $alasan = $_POST['alasan'];

    // Update status pengajuan judul di database
    $query = "UPDATE pengajuan_judul SET status='$status', alasan='$alasan' WHERE id='$id'";
    if (mysqli_query($conn, $query)) {
        $message = "Status judul berhasil diperbarui.";
    } else {
        $error = "Terjadi kesalahan saat memperbarui status judul.";
    }
}

// Menampilkan daftar pengajuan judul yang belum diseleksi
$query = "SELECT * FROM pengajuan_judul WHERE status='menunggu'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Prodi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Dashboard Prodi</h1>

    <!-- Menampilkan pesan sukses atau error -->
    <?php if (isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <!-- Tombol logout -->
    <a href="?logout=true" style="color: red; text-decoration: none;">Logout</a>

    <h2>Pengajuan Judul yang Menunggu Seleksi</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <tr>
                <th>Judul</th>
                <th>Abstrak</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <form method="POST" action="">
                    <tr>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['abstrak']) ?></td>
                        <td>
                            <!-- Pilihan untuk menerima atau menolak judul -->
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <select name="status" required>
                                <option value="diterima">Terima</option>
                                <option value="ditolak">Tolak</option>
                            </select><br><br>
                            <textarea name="alasan" placeholder="Alasan (optional)" rows="3"></textarea><br><br>
                            <button type="submit" name="seleksi">Proses Seleksi</button>
                        </td>
                    </tr>
                </form>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Tidak ada pengajuan judul yang menunggu seleksi.</p>
    <?php endif; ?>

</body>
</html>
