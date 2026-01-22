<?php
session_start();
include '../config/koneksi.php';

/* ======================
   PROTEKSI ADMIN
====================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

/* ======================
   PROSES VERIFIKASI
====================== */
if (isset($_POST['verifikasi'])) {

    $id_permohonan = mysqli_real_escape_string($koneksi, $_POST['id_permohonan']);
    $status        = mysqli_real_escape_string($koneksi, $_POST['status']);

    $file_pdf   = null;
    $file_excel = null;

    // Folder upload
    $dir_pdf   = "../uploads/permohonan/pdf/";
    $dir_excel = "../uploads/permohonan/excel/";

    if (!is_dir($dir_pdf)) mkdir($dir_pdf, 0777, true);
    if (!is_dir($dir_excel)) mkdir($dir_excel, 0777, true);

    /* Upload PDF */
    if (!empty($_FILES['file_pdf']['name'])) {
        $ext = pathinfo($_FILES['file_pdf']['name'], PATHINFO_EXTENSION);
        if ($ext === 'pdf') {
            $file_pdf = time() . '_' . $_FILES['file_pdf']['name'];
            move_uploaded_file($_FILES['file_pdf']['tmp_name'], $dir_pdf . $file_pdf);
        }
    }

    /* Upload Excel */
    if (!empty($_FILES['file_excel']['name'])) {
        $ext = pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION);
        if (in_array($ext, ['xls','xlsx','csv'])) {
            $file_excel = time() . '_' . $_FILES['file_excel']['name'];
            move_uploaded_file($_FILES['file_excel']['tmp_name'], $dir_excel . $file_excel);
        }
    }

    /* Update data */
    $sql = "UPDATE permohonan_data SET status_permohonan='$status'";

    if ($file_pdf) {
        $sql .= ", file_pdf='$file_pdf'";
    }
    if ($file_excel) {
        $sql .= ", file_excel='$file_excel'";
    }

    $sql .= " WHERE id_permohonan='$id_permohonan'";
    mysqli_query($koneksi, $sql);

    header("Location: permohonan_data.php");
    exit;
}

// AMBIL DATA PERMOHONAN
$data = mysqli_query($koneksi, "
    SELECT
        p.id_permohonan,
        p.format_data,
        p.tujuan_permohonan,
        p.status_permohonan,
        p.tanggal_permohonan,
        p.file_pdf,
        p.file_excel,
        u.nama_lengkap,
        p.judul_permohonan AS nama_katalog
    FROM permohonan_data p
    JOIN user u ON p.id_user = u.id_user
    ORDER BY p.tanggal_permohonan DESC
");

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kelola Permohonan Data - Admin</title>
    <link rel="stylesheet" href="style.css">
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
        <div class="permohonan-card">
            <div class="permohonan-title">Kelola Permohonan Data</div>

            <div class="table-wrap">
                <table class="table table-compact" style="width:100%">
                    <thead>
                    <tr>
                        <th style="width:60px">No</th>
                        <th>Nama Pemohon</th>
                        <th>Katalog Data</th>
                        <th>Format Data</th>
                        <th>Tujuan Permohonan</th>
                        <th>Status</th>
                        <th>File</th>
                        <th style="width:260px">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?= htmlspecialchars($row['nama_katalog']); ?></td>
                            <td><?= strtoupper(htmlspecialchars($row['format_data'])); ?></td>
                            <td><?= htmlspecialchars($row['tujuan_permohonan']); ?></td>
                            <td>
                                <?php $s = strtolower($row['status_permohonan']); ?>
                                <span class="status-badge status-<?= $s; ?>"><?= strtoupper($row['status_permohonan']); ?></span>
                            </td>
                            <td>
                                <?php if (!empty($row['file_pdf'])) { ?>
                                    <a class="file-link" href="../uploads/permohonan/pdf/<?= $row['file_pdf']; ?>" target="_blank">PDF</a><br>
                                <?php } ?>
                                <?php if (!empty($row['file_excel'])) { ?>
                                    <a class="file-link" href="../uploads/permohonan/excel/<?= $row['file_excel']; ?>" target="_blank">Excel</a>
                                <?php } ?>
                                <?php if (empty($row['file_pdf']) && empty($row['file_excel'])) echo "-"; ?>
                            </td>

                            <td>
                                <div style="margin-bottom:8px">
                                    <a class="btn-secondary" href="edit_permohonan.php?id=<?= $row['id_permohonan']; ?>">Edit</a>
                                </div>

                                <?php if ($row['status_permohonan'] == 'diajukan' || $row['status_permohonan'] == 'diproses') { ?>
                                    <form class="action-form" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_permohonan" value="<?= $row['id_permohonan']; ?>">
                                        <div class="action-row">
                                            <select name="status" class="small-input" required>
                                                <option value="">-- Pilih Status --</option>
                                                <option value="diproses">Diproses</option>
                                                <option value="disetujui">Disetujui</option>
                                                <option value="ditolak">Ditolak</option>
                                            </select>
                                            <label class="small-input" style="padding:6px 8px;background:transparent;border:0">Upload PDF<br><input type="file" name="file_pdf" accept="application/pdf"></label>
                                        </div>
                                        <div class="action-row">
                                            <label class="small-input" style="padding:6px 8px;background:transparent;border:0">Upload Excel<br><input type="file" name="file_excel" accept=".xls,.xlsx,.csv"></label>
                                            <button type="submit" name="verifikasi" class="btn-save">Simpan</button>
                                        </div>
                                    </form>
                                <?php } else { ?>
                                    -
                                <?php } ?>
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
