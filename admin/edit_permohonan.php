<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: permohonan_data.php");
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_permohonan = mysqli_real_escape_string($koneksi, $_POST['id_permohonan']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $tujuan = mysqli_real_escape_string($koneksi, $_POST['tujuan_permohonan']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul_permohonan']);

    $dir_pdf = "../uploads/permohonan/pdf/";
    $dir_excel = "../uploads/permohonan/excel/";
    if (!is_dir($dir_pdf)) mkdir($dir_pdf, 0777, true);
    if (!is_dir($dir_excel)) mkdir($dir_excel, 0777, true);

    // fetch existing filenames to possibly delete
    $q_old = mysqli_query($koneksi, "SELECT file_pdf, file_excel FROM permohonan_data WHERE id_permohonan='$id_permohonan'");
    $old = mysqli_fetch_assoc($q_old);

    $file_pdf = null;
    if (!empty($_FILES['file_pdf']['name'])) {
        $ext = pathinfo($_FILES['file_pdf']['name'], PATHINFO_EXTENSION);
        if ($ext === 'pdf') {
            $file_pdf = time() . '_' . basename($_FILES['file_pdf']['name']);
            move_uploaded_file($_FILES['file_pdf']['tmp_name'], $dir_pdf . $file_pdf);
            if (!empty($old['file_pdf']) && file_exists($dir_pdf . $old['file_pdf'])) unlink($dir_pdf . $old['file_pdf']);
        }
    }

    $file_excel = null;
    if (!empty($_FILES['file_excel']['name'])) {
        $ext = pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION);
        if (in_array($ext, ['xls','xlsx','csv'])) {
            $file_excel = time() . '_' . basename($_FILES['file_excel']['name']);
            move_uploaded_file($_FILES['file_excel']['tmp_name'], $dir_excel . $file_excel);
            if (!empty($old['file_excel']) && file_exists($dir_excel . $old['file_excel'])) unlink($dir_excel . $old['file_excel']);
        }
    }

    $sql = "UPDATE permohonan_data SET status_permohonan='$status', tujuan_permohonan='$tujuan', judul_permohonan='$judul'";
    if ($file_pdf) $sql .= ", file_pdf='$file_pdf'";
    if ($file_excel) $sql .= ", file_excel='$file_excel'";
    $sql .= " WHERE id_permohonan='$id_permohonan'";

    mysqli_query($koneksi, $sql);

    header("Location: permohonan_data.php");
    exit;
}

// ambil data
$q = mysqli_query($koneksi, "SELECT p.*, u.nama_lengkap FROM permohonan_data p JOIN user u ON p.id_user=u.id_user WHERE p.id_permohonan='$id' LIMIT 1");
$row = mysqli_fetch_assoc($q);
if (!$row) {
    header("Location: permohonan_data.php");
    exit;
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Permohonan</title>
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
            <div class="permohonan-title">Edit Permohonan</div>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_permohonan" value="<?= htmlspecialchars($row['id_permohonan']); ?>">

                <div style="margin-bottom:8px"><strong>Nama Pemohon:</strong> <?= htmlspecialchars($row['nama_lengkap']); ?></div>

                <div style="margin-bottom:8px">
                    <label><strong>Judul / Katalog</strong><br>
                        <input type="text" name="judul_permohonan" value="<?= htmlspecialchars($row['judul_permohonan']); ?>" class="small-input" style="width:100%">
                    </label>
                </div>

                <div style="margin-bottom:8px">
                    <label><strong>Tujuan Permohonan</strong><br>
                        <textarea name="tujuan_permohonan" class="small-input" style="width:100%" rows="4"><?= htmlspecialchars($row['tujuan_permohonan']); ?></textarea>
                    </label>
                </div>

                <div style="margin-bottom:8px">
                    <label><strong>Status</strong><br>
                        <select name="status" class="small-input" required>
                            <option value="diajukan" <?= $row['status_permohonan']==='diajukan'?'selected':'' ?>>Diajukan</option>
                            <option value="diproses" <?= $row['status_permohonan']==='diproses'?'selected':'' ?>>Diproses</option>
                            <option value="disetujui" <?= $row['status_permohonan']==='disetujui'?'selected':'' ?>>Disetujui</option>
                            <option value="ditolak" <?= $row['status_permohonan']==='ditolak'?'selected':'' ?>>Ditolak</option>
                        </select>
                    </label>
                </div>

                <div style="margin-bottom:8px">
                    <strong>File saat ini:</strong><br>
                    <?php if (!empty($row['file_pdf'])) { ?>
                        <a class="file-link" href="../uploads/permohonan/pdf/<?= $row['file_pdf']; ?>" target="_blank">PDF</a>
                    <?php } ?>
                    <?php if (!empty($row['file_excel'])) { ?>
                        <a class="file-link" href="../uploads/permohonan/excel/<?= $row['file_excel']; ?>" target="_blank">Excel</a>
                    <?php } ?>
                    <?php if (empty($row['file_pdf']) && empty($row['file_excel'])) echo '-'; ?>
                </div>

                <div style="margin-bottom:12px">
                    <label class="small-input">Ganti Upload PDF (opsional)<br><input type="file" name="file_pdf" accept="application/pdf"></label>
                    <label class="small-input">Ganti Upload Excel (opsional)<br><input type="file" name="file_excel" accept=".xls,.xlsx,.csv"></label>
                </div>

                <div style="display:flex;gap:8px">
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                    <a class="btn-secondary" href="permohonan_data.php">Batal</a>
                </div>

            </form>
        </div>

        <div style="height:18px"></div>
        <div class="footer">Pusdatin - Kementerian Pertanian © 2025</div>
    </main>
</div>

</body>
</html>
