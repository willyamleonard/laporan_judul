<?php
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Ambil peran pengguna dari session
$role = $_SESSION['role'];

// Arahkan pengguna ke halaman sesuai perannya
if ($role == 'mahasiswa') {
    header('Location: mahasiswa_dashboard.php');
    exit();
} elseif ($role == 'prodi') {
    header('Location: seleksi_judul.php');
    exit();
} elseif ($role == 'staff_prodi') {
    header('Location: staff_prodi_dashboard.php'); // Ganti dengan halaman yang sesuai untuk staff prodi
    exit();
} else {
    // Jika tidak ada peran yang valid, arahkan ke login
    header('Location: login.php');
    exit();
}
?>
