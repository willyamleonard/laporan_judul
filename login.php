<?php
session_start();
include('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_POST['nim'];
    $password = $_POST['password'];

    // Validasi NIM dan Password
    $query = "SELECT * FROM users WHERE nim = '$nim' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Simpan data user di session
        $_SESSION['role'] = $user['role'];
        $_SESSION['nim'] = $user['nim']; // Pastikan nim disimpan di session
        header('Location: index.php'); // Mengarahkan ke halaman utama setelah login
        exit();
    } else {
        echo "NIM atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Login</h2>
    <form method="POST" action="">
        <label for="nim">NIM</label>
        <input type="text" name="nim" required>
        
        <label for="password">Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</body>
</html>
