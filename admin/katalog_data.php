<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

//TAMBAH DATA
if (isset($_POST['tambah'])) {
    $file_pdf = null;

    // Pastikan direktori upload ada
    $upload_dir = __DIR__ . '/../uploads/katalog/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (!empty($_FILES['file_pdf']['name'])) {
        $nama_file = $_FILES['file_pdf']['name'];
        $tmp = $_FILES['file_pdf']['tmp_name'];
        $ext = pathinfo($nama_file, PATHINFO_EXTENSION);

        if (strtolower($ext) == 'pdf') {
            $file_pdf = time() . "_" . preg_replace('/[^A-Za-z0-9._-]/', '_', $nama_file);
            move_uploaded_file($tmp, $upload_dir . $file_pdf);
        }
    }


    // Amankan input sebelum dimasukkan ke DB
    $nama_data = mysqli_real_escape_string($koneksi, $_POST['nama_data'] ?? '');
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi'] ?? '');
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['kategori'] ?? '');
    $format_data = mysqli_real_escape_string($koneksi, $_POST['format_data'] ?? 'Dokumen');
    // gunakan nama_katalog jika tersedia, fallback ke nama_data
    $nama_katalog = mysqli_real_escape_string($koneksi, $_POST['nama_katalog'] ?? $nama_data);
    $file_pdf_esc = mysqli_real_escape_string($koneksi, $file_pdf ?? '');

    // Izinkan NULL untuk id_kategori jika tidak dipilih (hindari foreign key error)
    $id_kategori_sql = ($id_kategori === '' ? 'NULL' : "'" . mysqli_real_escape_string(
        $koneksi,
        $id_kategori
    ) . "'");

    mysqli_query($koneksi, "INSERT INTO katalog_data
        (nama_data, deskripsi, format_data, id_kategori, file_pdf, nama_katalog)
        VALUES (
            '$nama_data',
            '$deskripsi',
            '$format_data',
            $id_kategori_sql,
            '$file_pdf_esc',
            '$nama_katalog'
        )");

    header("Location: katalog_data.php");
    exit;
}

//UPDATE DATA
if (isset($_POST['update'])) {

    $id = mysqli_real_escape_string($koneksi, $_POST['id_data'] ?? '');
    $file_update = "";

    // Pastikan direktori upload ada
    $upload_dir = __DIR__ . '/../uploads/katalog/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (!empty($_FILES['file_pdf']['name'])) {
        $nama_file = $_FILES['file_pdf']['name'];
        $tmp = $_FILES['file_pdf']['tmp_name'];
        $ext = pathinfo($nama_file, PATHINFO_EXTENSION);

        if (strtolower($ext) == 'pdf') {
            $file_pdf = time() . "_" . preg_replace('/[^A-Za-z0-9._-]/', '_', $nama_file);
            move_uploaded_file($tmp, $upload_dir . $file_pdf);
            $file_update = ", file_pdf='" . mysqli_real_escape_string($koneksi, $file_pdf) . "'";
        }
    }



    $nama_data = mysqli_real_escape_string($koneksi, $_POST['nama_data'] ?? '');
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi'] ?? '');
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['kategori'] ?? '');
    $format_data = mysqli_real_escape_string($koneksi, $_POST['format_data'] ?? 'Dokumen');
    $nama_katalog = mysqli_real_escape_string($koneksi, $_POST['nama_katalog'] ?? $nama_data);

    $id_kategori_sql = ($id_kategori === '' ? 'NULL' : "'" . mysqli_real_escape_string($koneksi, $id_kategori) . "'");

    mysqli_query($koneksi, "UPDATE katalog_data SET
        nama_data='$nama_data',
        deskripsi='$deskripsi',
        format_data='$format_data',
        id_kategori=$id_kategori_sql,
        nama_katalog='$nama_katalog'
        $file_update
        WHERE id_data='$id'
    ");

    header("Location: katalog_data.php");
    exit;
}

//HAPUS DATA
if (isset($_GET['hapus'])) {
    $hapus_id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM katalog_data WHERE id_data='$hapus_id'");
    header("Location: katalog_data.php");
    exit;
}

// DATA EDIT

$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $edit = mysqli_fetch_assoc(
        mysqli_query($koneksi, "SELECT * FROM katalog_data WHERE id_data='$id_edit'")
    );
}

// Ambil daftar kategori untuk dropdown
$kategori_q = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$kategories = [];
while ($k = mysqli_fetch_assoc($kategori_q)) {
    $kategories[] = $k;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kelola Katalog Data</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .full{grid-column:1/-1}
        .form-actions{margin-top:10px}
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
            <h3 class="permohonan-title"><?= $edit ? "Edit Katalog Data" : "Tambah Katalog Data"; ?></h3>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_data" value="<?= $edit['id_data'] ?? ''; ?>">

                <div class="form-grid">
                    <div>
                        <label>Nama Data</label>
                        <input type="text" name="nama_data" required value="<?= htmlspecialchars($edit['nama_data'] ?? '') ?>" class="small-input">
                    </div>

                    <div>
                        <label>Format Data</label>
                        <input type="text" name="format_data" value="<?= htmlspecialchars($edit['format_data'] ?? '') ?>" class="small-input">
                    </div>

                    <div class="full">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="small-input" rows="3"><?= htmlspecialchars($edit['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label>Kategori</label>
                        <select name="kategori" class="small-input">
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategories as $kat): ?>
                                <option value="<?= $kat['id_kategori']; ?>" <?= (isset($edit['id_kategori']) && $edit['id_kategori'] == $kat['id_kategori']) ? 'selected' : ''; ?>><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Nama Katalog</label>
                        <input type="text" name="nama_katalog" value="<?= htmlspecialchars($edit['nama_katalog'] ?? '') ?>" class="small-input">
                    </div>

                    <div>
                        <label>File PDF</label>
                        <input type="file" name="file_pdf" accept="application/pdf">
                        <?php if (!empty($edit['file_pdf'])): ?>
                            <div style="margin-top:6px"><a class="file-link" href="../uploads/katalog/<?= $edit['file_pdf']; ?>" target="_blank">Lihat PDF saat ini</a></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <?php if ($edit): ?>
                        <button type="submit" name="update" class="btn-save">Update</button>
                        <a class="btn-secondary" href="katalog_data.php">Batal</a>
                    <?php else: ?>
                        <button type="submit" name="tambah" class="btn-save">Simpan</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div style="height:18px"></div>

        <div class="card">
            <h3 class="permohonan-title">Daftar Katalog Data</h3>
            <div class="table-wrap">
                <table class="table table-compact" style="width:100%">
                    <thead>
                    <tr>
                        <th style="width:60px">No</th>
                        <th>Nama Data</th>
                        <th>Format Data</th>
                        <th>Kategori</th>
                        <th>Nama Katalog</th>
                        <th>File</th>
                        <th style="width:160px">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    $query = mysqli_query($koneksi,
                        "SELECT kd.*, kat.nama_kategori AS nama_kategori FROM katalog_data kd LEFT JOIN kategori kat ON kd.id_kategori = kat.id_kategori ORDER BY kd.tanggal_input DESC"
                    );
                    while ($row = mysqli_fetch_assoc($query)) {
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama_data']); ?></td>
                            <td><?= htmlspecialchars(!empty($row['format_data']) ? $row['format_data'] : (!empty($row['file_pdf']) ? 'PDF' : '-')); ?></td>
                            <td><?= htmlspecialchars(!empty($row['nama_kategori']) ? $row['nama_kategori'] : '-'); ?></td>
                            <td><?= htmlspecialchars($row['nama_katalog'] ?? ''); ?></td>
                            <td>
                                <?php if (!empty($row['file_pdf'])) { ?>
                                    <a class="file-link" href="../uploads/katalog/<?= $row['file_pdf']; ?>" target="_blank">Lihat PDF</a>
                                <?php } else { echo "-"; } ?>
                            </td>
                            <td>
                                <a class="btn-secondary" href="?edit=<?= $row['id_data']; ?>">Edit</a>
                                <a class="btn-secondary" href="?hapus=<?= $row['id_data']; ?>" onclick="return confirm('Yakin menghapus data ini?')" style="margin-left:8px">Hapus</a>
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
