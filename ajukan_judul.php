<?php
session_start();
include('koneksi.php');

if ($_SESSION['role'] != 'mahasiswa') {
    header('Location: login.php');
}

$nim = $_SESSION['nim'];

if (isset($_POST['ajukan'])) {
    $judul = $_POST['judul'];
    $abstrak = $_POST['abstrak'];

    // Query untuk menyimpan pengajuan judul
    $query = "INSERT INTO pengajuan_judul (nim, judul, abstrak) VALUES ('$nim', '$judul', '$abstrak')";
    if (mysqli_query($conn, $query)) {
        echo "Judul berhasil diajukan!";
    } else {
        echo "Gagal mengajukan judul!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Judul</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Ajukan Judul</h1>
    <form method="POST" action="">
        <label for="judul">Judul</label>
        <input type="text" name="judul" required>

        <label for="abstrak">Abstrak</label>
        <textarea name="abstrak" required maxlength="150"></textarea>

        <button type="submit" name="ajukan">Ajukan Judul</button>
    </form>
</body>
</html>
