<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}
$role = $_SESSION['role'] ?? 'viewer';
$current = basename($_SERVER['PHP_SELF']);

// --- KONEKSI KE DATABASE ---
$conn = mysqli_connect("localhost", "root", "", "sistem_pemantauan_udara");
if (!$conn) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

// --- AMBIL DATA SENSOR DARI DATABASE ---
$sql = "SELECT measured_at, suhu, kelembaban, pm25_level 
        FROM air_quality_data 
        ORDER BY measured_at DESC 
        LIMIT 10";

$result = mysqli_query($conn, $sql);
$dataSensor = [];

if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $pm25 = $row['pm25_level'];
    if ($pm25 <= 50) {
      $status = "Baik";
    } elseif ($pm25 <= 100) {
      $status = "Sedang";
    } elseif ($pm25 <= 200) {
      $status = "Tidak Sehat";
    } else {
      $status = "Berbahaya";
    }

    $dataSensor[] = [
      $row['measured_at'],
      'Siskom 1',
      $row['suhu'],
      $row['kelembaban'],
      $row['pm25_level'],
      $status
    ];
  }
} else {
  $dataSensor = [["-", "-", "-", "-", "-", "-"]];
}

$latest = $dataSensor[0];

// --- DATA UNTUK GRAFIK ---
$chartQuery = "SELECT measured_at, suhu, kelembaban, pm25_level 
               FROM air_quality_data 
               ORDER BY measured_at DESC 
               LIMIT 10";
$chartResult = mysqli_query($conn, $chartQuery);

$labels = [];
$chartSuhu = [];
$chartKelembaban = [];
$chartPM25 = [];

if ($chartResult && mysqli_num_rows($chartResult) > 0) {
  while ($row = mysqli_fetch_assoc($chartResult)) {
    $labels[] = date('H:i', strtotime($row['measured_at']));
    $chartSuhu[] = (float)$row['suhu'];
    $chartKelembaban[] = (float)$row['kelembaban'];
    $chartPM25[] = (float)$row['pm25_level'];
  }

  $labels = array_reverse($labels);
  $chartSuhu = array_reverse($chartSuhu);
  $chartKelembaban = array_reverse($chartKelembaban);
  $chartPM25 = array_reverse($chartPM25);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard - Monitoring Udara</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{font-family:'Segoe UI';background:#f8f9fc;margin:0;}
header{background:#0046FF;padding:15px 25px;display:flex;justify-content:space-between;align-items:center;}
h1{color:white;}
nav a{
  margin-left:15px;
  text-decoration:none;
  color:black;
  font-weight:500;
  transition:color 0.3s ease;
}
nav a:hover{
  color:red;
}
nav a.active{
  color:red;
}
nav a.logout{
  font-weight:bold;
  color:black;
}
nav a.logout:hover{
  color:red;
}
.container{padding:25px;display:grid;grid-template-columns:repeat(4,1fr);gap:15px;}
.card{background:#87BAC3;border-radius:10px;padding:25px;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
.card h2{font-size:2.5em;margin:10px 0;}
.status{color:red;font-weight:bold;font-size:2em;}
.content{display:grid;grid-template-columns:1fr 1fr;gap:25px;padding:0 25px 25px;}
.chart,.table{background:#fff;border-radius:10px;padding:20px;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
table{width:100%;border-collapse:collapse;}
th,td{padding:8px;text-align:center;border-bottom:1px solid #eee;}
th{background:#87BAC3;}
</style>
</head>
<body>
<header>
  <h1>DASHBOARD</h1>
  <?php $current = basename($_SERVER['PHP_SELF']); ?>
  <nav>
    <a href="website.php" class="<?= $current=='website.php'?'active':'' ?>">Dashboard</a>
    <a href="sensor.php" class="<?= $current=='sensor.php'?'active':'' ?>">Sensor</a>
    <a href="grafik.php" class="<?= $current=='grafik.php'?'active':'' ?>">Grafik</a>
    <a href="alert.php" class="<?= $current=='alert.php'?'active':'' ?>">Alerts</a>
    <?php if ($role == 'admin'): ?>
      <a href="admin.php" class="<?= $current=='admin.php'?'active':'' ?>">Admin</a>
    <?php endif; ?>
    <a href="logout.php" class="logout">Logout</a>
  </nav>
</header>

<div class="container">
  <div class="card"><div>SUHU</div><h2><?= $latest[2] ?> °C</h2></div>
  <div class="card"><div>KELEMBABAN</div><h2><?= $latest[3] ?> %</h2></div>
  <div class="card"><div>PM2.5</div><h2><?= $latest[4] ?> µg/m³</h2></div>
  <div class="card"><div>STATUS</div><div class="status"><?= $latest[5] ?></div></div>
</div>

<div class="content">
  <div class="chart">
    <h3>Grafik Suhu, Kelembaban, dan PM2.5</h3>
    <canvas id="chartUdara"></canvas>
  </div>
  <div class="table">
    <h3>Data Sensor Terkini</h3>
    <table>
      <thead>
        <tr><th>Waktu</th><th>Lokasi</th><th>Suhu (°C)</th><th>Kelembaban (%)</th><th>PM2.5 (µg/m³)</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($dataSensor as $r): ?>
          <tr>
            <td><?= $r[0] ?></td>
            <td><?= $r[1] ?></td>
            <td><?= $r[2] ?></td>
            <td><?= $r[3] ?></td>
            <td><?= $r[4] ?></td>
            <td><?= $r[5] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const labels = <?= json_encode($labels) ?>;
const suhuData = <?= json_encode($chartSuhu) ?>;
const kelembabanData = <?= json_encode($chartKelembaban) ?>;
const pm25Data = <?= json_encode($chartPM25) ?>;

new Chart(document.getElementById('chartUdara'), {
  type: 'line',
  data: {
    labels: labels,
    datasets: [
      { label: 'Suhu (°C)', data: suhuData, borderColor: '#7b7fff', tension: 0.3, fill: false },
      { label: 'Kelembaban (%)', data: kelembabanData, borderColor: '#FFD700', tension: 0.3, fill: false },
      { label: 'PM2.5 (µg/m³)', data: pm25Data, borderColor: '#ff0000', tension: 0.3, fill: false }
    ]
  },
  options: {
    plugins: { legend: { display: true, position: 'top' } },
    scales: {
      x: { title: { display: true, text: 'Waktu (Jam)' } },
      y: { beginAtZero: true, title: { display: true, text: 'Nilai Sensor' } }
    }
  }
});
</script>
</body>
</html>
