<?php
session_start();
include '../config/koneksi.php';

if (isset($_POST['login'])) {

    $email    = $_POST['email'];
    $password = $_POST['password'];

    //LOGIN ADMIN
    $cek_admin = mysqli_query($koneksi,
        "SELECT * FROM admin 
         WHERE email='$email' 
         AND password='$password'"
    );

    if (mysqli_num_rows($cek_admin) > 0) {
        $_SESSION['role'] = 'admin';
        header("Location: ../admin/dashboard.php");
        exit;
    }

    //LOGIN USER (HARUS DISETUJUI)
    
    $cek_user = mysqli_query($koneksi,
        "SELECT * FROM user 
         WHERE email='$email' 
         AND password='$password'"
    );

    if (mysqli_num_rows($cek_user) > 0) {
        $user = mysqli_fetch_assoc($cek_user);

        if ($user['status_verifikasi'] == 'disetujui') {
            $_SESSION['role']    = 'user';
            $_SESSION['id_user'] = $user['id_user'];
            header("Location: ../user/dasboard.php");
            exit;
        } elseif ($user['status_verifikasi'] == 'pending') {
            header("Location: login.php?pesan=pending");
            exit;
        } else {
            header("Location: login.php?pesan=ditolak");
            exit;
        }
    }

    header("Location: login.php?pesan=gagal");
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk - SIMPELDATIN</title>
    <link rel="stylesheet" href="tampilan.css">
</head>
<body>

<div class="site-header">
    <div class="site-inner">
        <div class="site-brand">
            <img src="../upload/gambar/Logo_of_Ministry_of_Agriculture_of_the_Republic_of_Indonesia%20(1).png" alt="logo">
        </div>
        <div class="site-title">
            <div class="title-main">Pusat Data dan Sistem Informasi Pertanian</div>
            <div class="title-sub">Kementerian Pertanian</div>
        </div>
    </div>
</div>

<div class="auth-wrapper">
    <div class="auth-card">
        <h1>Masuk</h1>

        <?php
        if (isset($_GET['pesan'])) {
            if ($_GET['pesan'] == "gagal") {
                echo '<div class="message error">Login gagal! Email atau password salah.</div>';
            } elseif ($_GET['pesan'] == "pending") {
                echo '<div class="message warn">Akun Anda masih dalam proses verifikasi.</div>';
            } elseif ($_GET['pesan'] == "ditolak") {
                echo '<div class="message error">Akun Anda ditolak. Silakan hubungi admin.</div>';
            }
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input class="form-input" type="email" name="email" placeholder="email@email.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">Kata Sandi</label>
                <input class="form-input" type="password" name="password" placeholder="Kata Sandi (Minimal 8 karakter)" required>
            </div>

            <button class="btn-primary" type="submit" name="login">Masuk</button>

            <p class="muted">Belum punya akun ? <a class="link-light" href="daftar.php">Daftar</a></p>
        </form>
    </div>
</div>

<div class="auth-footer">
    <div class="footer-inner">
        <div class="footer-left">
            <div>📍 Gedung D, Lantai IV JL. Harsono RM No. 3, Ragunan, Jakarta Selatan 12550</div>
            <div>☎️ (021) 7816385</div>
            <div>✉️ layanan.data@pertanian.go.id</div>
            <div>⏰ Senin - Jum'at, 08.00 - 15.00</div>
        </div>
        <div class="footer-right">
            <div class="version">SIMPELDATIN V3.0</div>
            <div style="margin-top:6px; font-size:12px;">Pusdatin - Kementerian Pertanian © 2025</div>
        </div>
    </div>
</div>

</body>
</html>