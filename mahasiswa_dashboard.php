<?php
session_start();

// Pastikan pengguna sudah login dan memiliki role 'mahasiswa'
if ($_SESSION['role'] != 'mahasiswa') {
    header('Location: login.php');
    exit();
}

include('koneksi.php');

// Menangani pengajuan judul
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_judul'])) {
    $judul = $_POST['judul'];
    $abstrak = $_POST['abstrak'];
    $nim = $_SESSION['nim'];  // Mengambil NIM dari session

    // Validasi abstrak maksimal 150 kata
    if (str_word_count($abstrak) > 150) {
        $error = "Abstrak maksimal 150 kata.";
    } else {
        // Masukkan data pengajuan judul ke dalam database
        $query = "INSERT INTO pengajuan_judul (nim, judul, abstrak, status) VALUES ('$nim', '$judul', '$abstrak', 'menunggu')";
        if (mysqli_query($conn, $query)) {
            $success = "Pengajuan judul berhasil!";
        } else {
            $error = "Terjadi kesalahan dalam pengajuan judul.";
        }
    }
}

// Menangani upload bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_bukti_pembayaran'])) {
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
        $file_name = $_FILES['bukti_pembayaran']['name'];
        $file_tmp = $_FILES['bukti_pembayaran']['tmp_name'];
        $file_size = $_FILES['bukti_pembayaran']['size'];
        $file_error = $_FILES['bukti_pembayaran']['error'];

        // Tentukan lokasi upload file
        $upload_dir = 'uploads/pembayaran/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'png', 'jpeg', 'pdf'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid('bukti_', true) . '.' . $file_ext;
            $file_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                $nim = $_SESSION['nim'];
                // Update status pembayaran di database
                $query = "UPDATE pengajuan_judul SET bukti_pembayaran='$file_path', status_pembayaran='terverifikasi' WHERE nim='$nim' AND status='diterima'";
                if (mysqli_query($conn, $query)) {
                    $success_pembayaran = "Bukti pembayaran berhasil diupload!";
                } else {
                    $error_pembayaran = "Gagal memperbarui status pembayaran.";
                }
            } else {
                $error_pembayaran = "Gagal mengupload bukti pembayaran.";
            }
        } else {
            $error_pembayaran = "Tipe file tidak diperbolehkan.";
        }
    }
}

// Menangani logout
if (isset($_POST['logout'])) {
    session_destroy();  // Menghancurkan session
    header('Location: login.php');  // Mengalihkan ke halaman login
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Dashboard Mahasiswa</h1>

    <!-- Menampilkan pesan sukses atau error -->
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (isset($success_pembayaran)) echo "<p style='color:green;'>$success_pembayaran</p>"; ?>
    <?php if (isset($error_pembayaran)) echo "<p style='color:red;'>$error_pembayaran</p>"; ?>

    <p>Selamat datang, mahasiswa dengan NIM: <?= htmlspecialchars($_SESSION['nim']) ?></p>

    <h2>Menu Dashboard</h2>
    <ul>
        <li><a href="#ajukan-judul">Ajukan Judul Skripsi</a></li>
        <li><a href="#status-seleksi">Melihat Status Seleksi Judul</a></li>
        <li><a href="#pembayaran">Melakukan Pembayaran</a></li>
    </ul>

    <hr>

    <!-- Mengajukan Judul Skripsi -->
    <div id="ajukan-judul">
        <h3>Pengajuan Judul Skripsi</h3>
        <form method="POST" action="">
            <label for="judul">Judul Proposal Skripsi</label>
            <input type="text" name="judul" required><br><br>

            <label for="abstrak">Abstrak (Maksimal 150 kata)</label>
            <textarea name="abstrak" rows="6" required></textarea><br><br>

            <button type="submit" name="submit_judul">Ajukan Judul</button>
        </form>
    </div>

    <hr>

    <!-- Melihat Status Seleksi Judul -->
    <div id="status-seleksi">
        <h3>Status Pengajuan Judul</h3>
        <?php
        // Cek status pengajuan judul
        $nim = $_SESSION['nim'];
        $query = "SELECT * FROM pengajuan_judul WHERE nim='$nim' ORDER BY tanggal_pengajuan DESC LIMIT 1";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            echo "<p>Judul: " . htmlspecialchars($row['judul']) . "</p>";
            echo "<p>Abstrak: " . htmlspecialchars($row['abstrak']) . "</p>";
            echo "<p>Status: " . htmlspecialchars($row['status']) . "</p>";

            // Jika judul diterima atau ditolak, tampilkan alasan
            if ($row['status'] == 'ditolak' && !empty($row['alasan'])) {
                echo "<p>Alasan Ditolak: " . htmlspecialchars($row['alasan']) . "</p>";
            }
        } else {
            echo "<p>Anda belum mengajukan judul.</p>";
        }
        ?>
    </div>

    <hr>

    <!-- Melakukan Pembayaran -->
    <div id="pembayaran">
        <h3>Upload Bukti Pembayaran</h3>
        <?php
        // Cek apakah status pengajuan sudah diterima
        $query = "SELECT * FROM pengajuan_judul WHERE nim='$nim' AND status='diterima'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            echo '<form method="POST" enctype="multipart/form-data">
                    <label for="bukti_pembayaran">Pilih file bukti pembayaran (jpg, png, jpeg, pdf):</label>
                    <input type="file" name="bukti_pembayaran" required><br><br>
                    <button type="submit" name="submit_bukti_pembayaran">Upload Bukti Pembayaran</button>
                </form>';
        } else {
            echo "<p>Pengajuan judul Anda belum diterima. Anda tidak dapat mengupload bukti pembayaran.</p>";
        }
        ?>
    </div>

    <hr>

    <!-- Tombol Logout -->
    <form method="POST" action="">
        <button type="submit" name="logout">Logout</button>
    </form>
</body>
</html>
