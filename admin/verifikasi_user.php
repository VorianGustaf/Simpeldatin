<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// proses verifikasi
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($_GET['aksi'] == 'setujui') {
        mysqli_query($koneksi, "UPDATE user SET status_verifikasi='disetujui' WHERE id_user='$id'");
    }

    if ($_GET['aksi'] == 'tolak') {
        mysqli_query($koneksi, "UPDATE user SET status_verifikasi='ditolak' WHERE id_user='$id'");
    }

    header("Location: verifikasi_user.php");
    exit;
}

// ambil user pending
$query = mysqli_query($koneksi, "SELECT * FROM user WHERE status_verifikasi='pending'");
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verifikasi User - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>/* small inline tweak for table cells */
        .table th, .table td{white-space:nowrap}
    </style>
</head>
<body>

<div class="header">
    <div class="logo">
        <img src="../upload/gambar/Logo_of_Ministry_of_Agriculture_of_the_Republic_of_Indonesia%20(1).png" alt="logo">
        <div class="title">SIMPELDATIN - Admin</div>
    </div>
    <div class="user">Admin &nbsp;|&nbsp; <a href="../auth/logout.php" style="color:#fff; text-decoration:none">Logout</a></div>
</div>

<div class="wrap">
    <aside class="sidebar">
        <ul class="nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="verifikasi_user.php">Verifikasi User</a></li>
            <li><a href="permohonan_data.php">Kelola Permohonan Data</a></li>
            <li><a href="katalog_data.php">Kelola Katalog Data</a></li>
        </ul>
    </aside>

    <main class="content">
        <div class="card">
            <div class="card-header">
                <div class="title">
                    <div class="page-title">Verifikasi User SIMPELDATIN</div>
                    <div class="breadcrumb">Daftar akun yang menunggu persetujuan</div>
                </div>
                <div class="card-actions">
                    <a href="dashboard.php" class="btn btn-outline">⬅ Kembali ke Dashboard</a>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-striped" style="width:100%">
                    <thead>
                    <tr>
                        <th style="width:60px">No</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Email</th>
                        <th>Instansi</th>
                        <th style="width:160px">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    while ($data = mysqli_fetch_assoc($query)) {
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($data['nama_lengkap']); ?></td>
                            <td><?= htmlspecialchars($data['nik']); ?></td>
                            <td><?= htmlspecialchars($data['email']); ?></td>
                            <td><?= htmlspecialchars($data['instansi']); ?></td>
                            <td>
                                <a class="btn btn-approve" href="?aksi=setujui&id=<?= $data['id_user']; ?>">Setujui</a>
                                <a class="btn btn-reject" href="?aksi=tolak&id=<?= $data['id_user']; ?>" onclick="return confirm('Yakin menolak user ini?')">Tolak</a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="height:18px"></div>
        <div class="footer">Pusdatin - Kementerian Pertanian © 2025</div>
    </main>
</div>

</body>
</html>
