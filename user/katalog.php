<?php
session_start();
include '../config/koneksi.php';

// PROTEKSI USER
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

// AMBIL DATA KATALOG (dengan pencarian)
$q = '';
$where = '';
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $q = trim($_GET['q']);
    $q_esc = mysqli_real_escape_string($koneksi, $q);
    $where = " WHERE nama_data LIKE '%" . $q_esc . "%' OR deskripsi LIKE '%" . $q_esc . "%' OR format_data LIKE '%" . $q_esc . "%'";
}

$sql = "SELECT 
        id_data,
        nama_data,
        deskripsi,
        format_data,
        tanggal_upload,
        file_pdf,
        file_excel
    FROM katalog_data" . $where . " ORDER BY tanggal_upload DESC";

$data = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Katalog Data</title>
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
        <div class="card">
            <div class="controls">
                <div class="left">
                    <h2>Katalog Data</h2>
                </div>
                <div class="right">
                    <label>Show <select><option>10</option><option>25</option></select> entries</label>
                    <div style="display:inline-block; margin-left:12px;">
                        <input id="catalogSearch" type="search" name="q" placeholder="Search" value="<?= htmlspecialchars($q); ?>" style="padding:6px;">
                    </div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Data</th>
                        <th>Deskripsi</th>
                        <th>Format Data</th>
                        <th>Tanggal Upload</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                <?php 
                $no = 1;
                if (mysqli_num_rows($data) > 0) {
                    while ($row = mysqli_fetch_assoc($data)) { ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['nama_data']); ?></td>
                    <td><?= htmlspecialchars($row['deskripsi']); ?></td>
                    <td><?= strtoupper($row['format_data']); ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tanggal_upload'])); ?></td>
                    <td>
                        <?php if (!empty($row['file_pdf'])) { ?>
                            <a href="../uploads/katalog/pdf/<?= $row['file_pdf']; ?>" target="_blank">Lihat PDF</a><br>
                        <?php } ?>

                        <?php if (!empty($row['file_excel'])) { ?>
                            <a href="../uploads/katalog/excel/<?= $row['file_excel']; ?>" target="_blank">Download Excel</a>
                        <?php } ?>

                        <?php if (empty($row['file_pdf']) && empty($row['file_excel'])) { ?>
                            -
                        <?php } ?>
                    </td>
                </tr>
                <?php } } else { ?>
                <tr>
                    <td colspan="6" align="center">Data katalog belum tersedia</td>
                </tr>
                <?php } ?>

                </tbody>
            </table>
        </div>
    </main>
</div>

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

<script>
// Client-side live search for katalog table (debounced)
document.addEventListener('DOMContentLoaded', function(){
    const input = document.getElementById('catalogSearch');
    const table = document.querySelector('table tbody');
    if (!input || !table) return;

    function highlight(text, term){
        if(!term) return text;
        const re = new RegExp('('+term.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')','ig');
        return text.replace(re, '<mark>$1</mark>');
    }

    function filterRows(q){
        const term = q.trim().toLowerCase();
        for (const row of table.querySelectorAll('tr')){
            const cells = Array.from(row.querySelectorAll('td'));
            if(cells.length === 0) continue;
            const name = (cells[1]?.textContent||'').toLowerCase();
            const desc = (cells[2]?.textContent||'').toLowerCase();
            const fmt  = (cells[3]?.textContent||'').toLowerCase();
            const match = term === '' || name.includes(term) || desc.includes(term) || fmt.includes(term);
            row.style.display = match ? '' : 'none';
            // optionally highlight matches in visible rows
            if(match){
                cells[1].innerHTML = highlight(cells[1].textContent, term);
                cells[2].innerHTML = highlight(cells[2].textContent, term);
                cells[3].innerHTML = highlight(cells[3].textContent, term);
            } else {
                // reset to plain text to remove marks
                cells[1].textContent = cells[1].textContent;
                cells[2].textContent = cells[2].textContent;
                cells[3].textContent = cells[3].textContent;
            }
        }
    }

    // debounce helper
    let tId = null;
    input.addEventListener('input', function(e){
        clearTimeout(tId);
        tId = setTimeout(function(){ filterRows(input.value); }, 180);
    });

    // perform initial filter if server-side q provided
    if (input.value && input.value.trim() !== '') filterRows(input.value);
});
</script>

</body>
</html>
