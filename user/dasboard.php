<?php
session_start();
include '../config/koneksi.php';

// PROTEKSI USER

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

//  AMBIL DATA PERMOHONAN (cek keberadaan kolom resi/tracking untuk kompatibilitas)

// periksa apakah kolom no_resi dan tracking_permohonan ada di tabel
$has_no_resi = false;
$has_tracking = false;
$qc = mysqli_query($koneksi, "SHOW COLUMNS FROM permohonan_data LIKE 'no_resi'");
if ($qc && mysqli_num_rows($qc) > 0) $has_no_resi = true;
$qc2 = mysqli_query($koneksi, "SHOW COLUMNS FROM permohonan_data LIKE 'tracking'");
if ($qc2 && mysqli_num_rows($qc2) > 0) $has_tracking = true;

// build select parts conditionally (fall back to empty string if not present)
$select_extra = "";
if ($has_no_resi) $select_extra .= "p.no_resi, "; else $select_extra .= "'' AS no_resi, ";
if ($has_tracking) $select_extra .= "p.tracking, "; else $select_extra .= "'' AS tracking, ";

$sql = "SELECT 
        p.id_permohonan,
        p.status_permohonan,
        p.tanggal_permohonan,
        k.nama_katalog,
        k.format_data,
            p.file_pdf,
            p.file_excel,
            " . $select_extra . "
            p.judul_permohonan,
            p.tujuan_permohonan
    FROM permohonan_data p
    LEFT JOIN katalog_data k ON p.judul_permohonan = k.nama_katalog
    WHERE p.id_user = '$id_user'
    ORDER BY p.tanggal_permohonan DESC";

$data = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard User</title>
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
        <div class="action-row">
            <a class="btn-app" href="permohonan.php"><span class="icon">✏️</span>Buat Permohonan Data</a>
            <a class="btn-app" href="katalog.php"><span class="icon">🗂️</span>Katalog Data</a>
            <a class="btn-app" href="#"><span class="icon">❓</span>Bantuan/Pengaduan</a>
        </div>

        <div class="card">
            <div class="controls">
                <div class="left">
                    <h2>Riwayat Permohonan Data</h2>
                </div>
                <div class="right">
                    <label>Show <select><option>10</option><option>25</option></select> entries</label>
                    <input type="search" placeholder="Search" />
                </div>
            </div>

            <table>
                <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Permohonan</th>
                    <th>Tujuan Permohonan</th>
                    <th>Tracking Permohonan</th>
                    <th>File</th>
                </tr>
                </thead>
                <tbody>

                <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tanggal_permohonan'])); ?></td>
                    <td style="max-width:420px"><?= nl2br(htmlspecialchars($row['tujuan_permohonan'] ?? '-')); ?></td>
                    <td>
                        <div style="font-weight:700"><?= strtoupper($row['status_permohonan']); ?></div>
                    </td>
                    <td>
                        <?php if (!empty($row['file_pdf'] ?? '') || !empty($row['file_excel'] ?? '')) { ?>
                            <?php if (!empty($row['file_pdf'] ?? '')) { ?>
                                <a class="file-link" href="../uploads/permohonan/pdf/<?= $row['file_pdf'] ?? ''; ?>" target="_blank">PDF</a>
                            <?php } ?>
                            <?php if (!empty($row['file_excel'] ?? '')) { ?>
                                &nbsp;|&nbsp;<a class="file-link" href="../uploads/permohonan/excel/<?= $row['file_excel'] ?? ''; ?>" target="_blank">Excel</a>
                            <?php } ?>
                        <?php } else { ?>
                            -
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>

                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('menuBtn');
    const sidebar = document.querySelector('.sidebar');
    if(!btn || !sidebar) return;

    // create backdrop
    let backdrop = document.querySelector('.backdrop');
    if(!backdrop){
        backdrop = document.createElement('div');
        backdrop.className = 'backdrop';
        document.body.appendChild(backdrop);
    }

    function openSidebar(){
        sidebar.classList.add('open');
        backdrop.classList.add('visible');
    }
    function closeSidebar(){
        sidebar.classList.remove('open');
        backdrop.classList.remove('visible');
    }

    btn.addEventListener('click', function(){
        if(window.innerWidth > 900){
            // desktop: toggle collapsed state
            document.body.classList.toggle('sidebar-collapsed');
        } else {
            if(sidebar.classList.contains('open')) closeSidebar(); else openSidebar();
        }
    });

    backdrop.addEventListener('click', closeSidebar);

    window.addEventListener('resize', function(){
        if(window.innerWidth > 900){
            // ensure sidebar visible and backdrop hidden on desktop
            sidebar.classList.remove('open');
            backdrop.classList.remove('visible');
        }
    });
});
</script>
</html>
