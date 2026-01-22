<?php
include '../config/koneksi.php';

$pesan = "";
$berhasil = false;

if (isset($_POST['daftar'])) {

    $nama_lengkap = $_POST['nama_lengkap'];
    $nik          = $_POST['nik'];
    $gender       = $_POST['gender'];
    $pekerjaan    = $_POST['pekerjaan'];
    $instansi     = $_POST['instansi'];
    $email        = $_POST['email'];
    $no_hp        = $_POST['no_hp'];
    $password     = $_POST['password'];

    // cek NIK
    $cekNik = mysqli_query($koneksi, "SELECT * FROM user WHERE nik='$nik'");
    if (mysqli_num_rows($cekNik) > 0) {
        $pesan = "NIK sudah digunakan.";
    } else {

        // cek email
        $cekEmail = mysqli_query($koneksi, "SELECT * FROM user WHERE email='$email'");
        if (mysqli_num_rows($cekEmail) > 0) {
            $pesan = "Email sudah digunakan.";
        } else {

            // simpan user
            $simpan = mysqli_query($koneksi, "
                INSERT INTO user 
                (nama_lengkap, nik, gender, pekerjaan, instansi, email, no_hp, password)
                VALUES 
                ('$nama_lengkap', '$nik', '$gender', '$pekerjaan', '$instansi', '$email', '$no_hp', '$password')
            ");

            if ($simpan) {
                $berhasil = true;
            } else {
                $pesan = "Pendaftaran gagal.";
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Daftar - SIMPELDATIN</title>
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
        <h1>Daftar</h1>

        <?php if ($berhasil) { ?>

            <div class="message" style="color:#bff0b0; text-align:center;">
                Pendaftaran berhasil.<br>
                Silakan tunggu proses verifikasi oleh admin sebelum melakukan login.
            </div>
            <p style="text-align:center; margin-top:12px;"><a class="link-light" href="login.php">Kembali ke Halaman Login</a></p>

        <?php } else { ?>

            <?php if ($pesan != "") { ?>
                <div class="message error"><?php echo htmlspecialchars($pesan); ?></div>
            <?php } ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input class="form-input" type="text" name="nama_lengkap" required>
                </div>

                <div class="form-group">
                    <label class="form-label">NIK</label>
                    <input class="form-input" type="text" name="nik" maxlength="16" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Jenis Kelamin</label>
                    <select class="form-input" name="gender" required style="padding:10px;">
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Pekerjaan</label>
                    <input class="form-input" type="text" name="pekerjaan">
                </div>

                <div class="form-group">
                    <label class="form-label">Instansi</label>
                    <input class="form-input" type="text" name="instansi">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input class="form-input" type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label class="form-label">No HP</label>
                    <input class="form-input" type="text" name="no_hp">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input class="form-input" type="password" name="password" required>
                </div>

                <button class="btn-primary" type="submit" name="daftar">Daftar</button>

                <p class="muted">Sudah punya akun? <a class="link-light" href="login.php">Masuk</a></p>
            </form>

        <?php } ?>

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
