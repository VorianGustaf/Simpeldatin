<?php
session_start();
include '../config/koneksi.php';

// proteksi halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// total user
$q_user = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM user");
$user = mysqli_fetch_assoc($q_user);

// user pending
$q_pending = mysqli_query($koneksi,
    "SELECT COUNT(*) AS total FROM user WHERE status_verifikasi='pending'");
$user_pending = mysqli_fetch_assoc($q_pending);

// total permohonan
$q_permohonan = mysqli_query($koneksi,
    "SELECT COUNT(*) AS total FROM permohonan_data");
$permohonan = mysqli_fetch_assoc($q_permohonan);

// permohonan diajukan
$q_permohonan_pending = mysqli_query($koneksi,
    "SELECT COUNT(*) AS total FROM permohonan_data WHERE status_permohonan='diajukan'");
$permohonan_pending = mysqli_fetch_assoc($q_permohonan_pending);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Admin</title>
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
        <div class="metrics">
            <div class="card">
                <h3>Total User</h3>
                <div class="value"><?= $user['total']; ?></div>
            </div>
            <div class="card">
                <h3>User Menunggu Verifikasi</h3>
                <div class="value"><?= $user_pending['total']; ?></div>
            </div>
            <div class="card">
                <h3>Total Permohonan</h3>
                <div class="value"><?= $permohonan['total']; ?></div>
            </div>
            <div class="card">
                <h3>Permohonan Diajukan</h3>
                <div class="value"><?= $permohonan_pending['total']; ?></div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="widget">
                    <h4>Grafik Permohonan (12 bulan terakhir)</h4>
                    <canvas id="permohonanChart" height="220"></canvas>
                </div>
            </div>
            <div class="col">
                <div class="widget">
                    <h4>Ringkasan / Notifikasi</h4>
                    <div class="notif-list">
                        <div class="notif-item">
                            <div class="notif-icon">👤</div>
                            <div class="notif-body">
                                <p class="notif-title">User Menunggu Verifikasi</p>
                                <p class="notif-desc"><?= $user_pending['total'] > 0 ? $user_pending['total'] . ' akun baru menunggu verifikasi' : 'Tidak ada user menunggu verifikasi'; ?></p>
                            </div>
                            <div class="notif-actions">
                                <div class="notif-badge"><?= $user_pending['total']; ?></div>
                                <a class="notif-link" href="verifikasi_user.php">Lihat</a>
                            </div>
                        </div>

                        <div class="notif-item">
                            <div class="notif-icon">📄</div>
                            <div class="notif-body">
                                <p class="notif-title">Permohonan Tertunda</p>
                                <p class="notif-desc"><?= $permohonan_pending['total'] > 0 ? 'Ada ' . $permohonan_pending['total'] . ' permohonan tertunda' : 'Tidak ada permohonan tertunda'; ?></p>
                            </div>
                            <div class="notif-actions">
                                <div class="notif-badge"><?= $permohonan_pending['total']; ?></div>
                                <a class="notif-link" href="permohonan_data.php">Kelola</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="widget" style="margin-top:18px">
                    <h4>Recent Permohonan</h4>
                    <table class="table">
                        <thead><tr><th>No</th><th>Judul</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php
                        $q_recent = mysqli_query($koneksi, "SELECT id_permohonan, judul_permohonan, status_permohonan FROM permohonan_data ORDER BY tanggal_permohonan DESC LIMIT 6");
                        $i=1; while($r = mysqli_fetch_assoc($q_recent)){
                        ?>
                            <tr><td><?= $i++; ?></td><td><?= htmlspecialchars($r['judul_permohonan']); ?></td><td><?= htmlspecialchars($r['status_permohonan']); ?></td></tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="footer">Pusdatin - Kementerian Pertanian © 2025</div>
    </main>
</div>

</body>
</html>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php
// prepare data: count permohonan per month for last 12 months
$months = [];
$counts = [];
$q = mysqli_query($koneksi, "SELECT DATE_FORMAT(tanggal_permohonan, '%Y-%m') AS ym, COUNT(*) AS cnt
    FROM permohonan_data
    WHERE tanggal_permohonan >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
    GROUP BY ym
    ORDER BY ym ASC");

$map = [];
while ($r = mysqli_fetch_assoc($q)) {
    $map[$r['ym']] = (int)$r['cnt'];
}

// build labels for last 12 months
for ($i = 11; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-{$i} months"));
    $months[] = date('M Y', strtotime($m . '-01'));
    $counts[] = isset($map[$m]) ? $map[$m] : 0;
}

?>
<script>
const ctx = document.getElementById('permohonanChart').getContext('2d');
const labels = <?= json_encode($months); ?>;
const data = <?= json_encode($counts); ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Permohonan per Bulan',
            data: data,
            borderColor: '#2f8f2f',
            backgroundColor: 'rgba(47,143,47,0.12)',
            tension: 0.3,
            fill: true,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true }
        }
    }
});
</script>
