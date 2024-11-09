<?php
session_start();

// Pastikan hanya staff prodi yang bisa mengakses halaman ini
if ($_SESSION['role'] != 'staff_prodi') {
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

// Menangani upload surat pengantar pembimbing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_surat'])) {
    $id_mahasiswa = $_POST['id_mahasiswa'];
    $file_name = $_FILES['surat']['name'];
    $file_tmp = $_FILES['surat']['tmp_name'];
    $file_size = $_FILES['surat']['size'];
    $file_error = $_FILES['surat']['error'];

    // Validasi file upload
    if ($file_error === 0) {
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = array('pdf', 'docx', 'jpg', 'png');
        
        if (in_array($file_ext, $allowed_ext)) {
            if ($file_size <= 5000000) { // Maksimal 5MB
                $file_new_name = uniqid('', true) . '.' . $file_ext;
                $file_dest = 'uploads/' . $file_new_name;
                
                if (move_uploaded_file($file_tmp, $file_dest)) {
                    // Simpan ke database
                    $query = "INSERT INTO surat_pengantar (id_mahasiswa, file_path) VALUES ('$id_mahasiswa', '$file_dest')";
                    if (mysqli_query($conn, $query)) {
                        $message = "Surat pengantar berhasil diupload.";
                    } else {
                        $error = "Terjadi kesalahan saat menyimpan data ke database.";
                    }
                } else {
                    $error = "Terjadi kesalahan saat mengupload file.";
                }
            } else {
                $error = "Ukuran file terlalu besar. Maksimal 5MB.";
            }
        } else {
            $error = "Tipe file tidak diperbolehkan. Hanya PDF, DOCX, JPG, dan PNG yang diterima.";
        }
    } else {
        $error = "Terjadi kesalahan saat mengupload file.";
    }
}

// Membuat laporan rekapitulasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_laporan'])) {
    // Query untuk menghitung jumlah judul diterima dan jumlah pembayaran yang lunas
    $query = "SELECT 
                (SELECT COUNT(*) FROM pengajuan_judul WHERE status='diterima') AS diterima,
                (SELECT COUNT(*) FROM pembayaran WHERE status='lunas') AS lunas";
    $result = mysqli_query($conn, $query);
    $rekap = mysqli_fetch_assoc($result);
    $diterima = $rekap['diterima'];
    $lunas = $rekap['lunas'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Staff Prodi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Dashboard Staff Prodi</h1>

    <!-- Menampilkan pesan sukses atau error -->
    <?php if (isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <!-- Tombol logout -->
    <a href="?logout=true" style="color: red; text-decoration: none;">Logout</a>

    <!-- Form untuk upload surat pengantar pembimbing -->
    <h2>Upload Surat Pengantar Pembimbing</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="id_mahasiswa">ID Mahasiswa:</label>
        <input type="text" name="id_mahasiswa" required><br><br>
        <label for="surat">Pilih File Surat Pengantar:</label>
        <input type="file" name="surat" required><br><br>
        <button type="submit" name="upload_surat">Upload Surat</button>
    </form>

    <hr>

    <!-- Form untuk membuat laporan rekapitulasi -->
    <h2>Buat Laporan Rekapitulasi</h2>
    <form method="POST">
        <button type="submit" name="generate_laporan">Generate Laporan</button>
    </form>

    <?php if (isset($diterima) && isset($lunas)): ?>
        <h3>Laporan Rekapitulasi</h3>
        <p>Jumlah Judul Diterima: <?= $diterima ?></p>
        <p>Jumlah Pembayaran Lunas: <?= $lunas ?></p>
    <?php endif; ?>

</body>
</html>
