<?php
session_start();
include '../config/koneksi.php';

// PROTEKSI USER
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// PROSES SIMPAN PERMOHONAN
if (isset($_POST['kirim'])) {

    // ambil judul dari pilihan katalog (nama_katalog) dan simpan sebagai judul_permohonan
    $judul   = mysqli_real_escape_string($koneksi, $_POST['id_data'] ?? '');
    $tujuan  = mysqli_real_escape_string($koneksi, $_POST['tujuan'] ?? '');
    $format  = mysqli_real_escape_string($koneksi, $_POST['format'] ?? '');

    mysqli_query($koneksi, "
        INSERT INTO permohonan_data 
        (id_user, judul_permohonan, tujuan_permohonan, bentuk_data, status_permohonan, tanggal_permohonan)
        VALUES
        ('$id_user', '$judul', '$tujuan', '$format', 'diajukan', NOW())
    ");

    header("Location: permohonan.php?sukses=1");
    exit;
}

// AMBIL DATA KATALOG
$katalog = mysqli_query($koneksi, "SELECT * FROM katalog_data");

// ambil data user untuk prefill
$user = null;
if (!empty($id_user)) {
    $uq = mysqli_query($koneksi, "SELECT * FROM user WHERE id_user = '" . mysqli_real_escape_string($koneksi, $id_user) . "' LIMIT 1");
    if ($uq && mysqli_num_rows($uq) > 0) $user = mysqli_fetch_assoc($uq);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Form Permohonan Data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="user-topbar">
    <button id="menuBtn" class="menu" aria-label="Toggle menu">☰</button>
    <div class="brand">
        <img src="../upload/gambar/Logo_of_Ministry_of_Agriculture_of_the_Republic_of_Indonesia%20(1).png" alt="logo">
        <div class="title">SIMPELDATIN</div>
    </div>
    <div class="actions">
        <a href="../auth/logout.php" style="color:#fff; text-decoration:none;">Logout</a>
    </div>
</div>

<div class="layout">
    <aside class="sidebar">
        <ul class="nav">
            <li><a href="dasboard.php"><span class="icon">🏠</span><span class="label">Dashboard</span></a></li>
            <li><a href="permohonan.php"><span class="icon">✏️</span><span class="label">Form Permohonan</span></a></li>
            <li><a href="katalog.php"><span class="icon">🗂️</span><span class="label">Katalog Data</span></a></li>
            <li><a href="#"><span class="icon">❓</span><span class="label">Bantuan</span></a></li>
        </ul>
    </aside>

    <main class="content">
        <div class="center-wrapper">
            <div class="permohonan-card">
                <h1>Form Permohonan Data</h1>

                <?php if (isset($_GET['sukses'])) { ?>
                    <p style="color:#bff0b0; text-align:center;">Permohonan berhasil dikirim dan menunggu verifikasi admin.</p>
                <?php } ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap'] ?? ''); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>NIK</label>
                        <input type="text" name="nik" value="<?= htmlspecialchars($user['nik'] ?? ''); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Pekerjaan</label>
                        <input type="text" name="pekerjaan" value="<?= htmlspecialchars($user['pekerjaan'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Instansi</label>
                        <input type="text" name="instansi" value="<?= htmlspecialchars($user['instansi'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki" <?= (isset($user['gender']) && $user['gender']=='Laki-laki')? 'selected':''; ?>>Laki - laki</option>
                            <option value="Perempuan" <?= (isset($user['gender']) && $user['gender']=='Perempuan')? 'selected':''; ?>>Perempuan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>No. Telepon / HP</label>
                        <input type="text" name="no_hp" value="<?= htmlspecialchars($user['no_hp'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Pilih Katalog Data</label>
                        <select name="id_data" required>
                            <option value="">-- Pilih Data --</option>
                            <?php mysqli_data_seek($katalog,0); while ($row = mysqli_fetch_assoc($katalog)) { ?>
                                <option value="<?= htmlspecialchars($row['nama_katalog']); ?>"><?= $row['nama_katalog']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Keperluan</label>
                        <select name="format" required>
                            <option value="">-- Pilih Keperluan --</option>
                            <option value="PDF">PDF</option>
                            <option value="Excel">Excel</option>
                            <option value="Tabel">Tabel</option>
                            <option value="Grafik">Grafik</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Upload Surat Permohonan (Opsional) *Max. 1 MB, Format .pdf</label>
                        <input type="file" name="surat_permohonan" accept="application/pdf">
                    </div>

                    <div class="form-group">
                        <label>Detail Permohonan Data</label>
                        <textarea name="tujuan" rows="5"></textarea>
                    </div>

                    <button class="btn-submit" type="submit" name="kirim">Kirim Permohonan</button>
                </form>
            </div>
        </div>
    </main>
</div>

<!-- reuse the small script for menu toggle (same as dashboard) -->
<script>
document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('menuBtn');
    const sidebar = document.querySelector('.sidebar');
    if(!btn || !sidebar) return;

    let backdrop = document.querySelector('.backdrop');
    if(!backdrop){
        backdrop = document.createElement('div');
        backdrop.className = 'backdrop';
        document.body.appendChild(backdrop);
    }

    function openSidebar(){ sidebar.classList.add('open'); backdrop.classList.add('visible'); }
    function closeSidebar(){ sidebar.classList.remove('open'); backdrop.classList.remove('visible'); }

    btn.addEventListener('click', function(){
        if(window.innerWidth > 900){ document.body.classList.toggle('sidebar-collapsed'); }
        else { if(sidebar.classList.contains('open')) closeSidebar(); else openSidebar(); }
    });

    backdrop.addEventListener('click', closeSidebar);
    window.addEventListener('resize', function(){ if(window.innerWidth > 900){ sidebar.classList.remove('open'); backdrop.classList.remove('visible'); } });
});
</script>

</body>
</html>
