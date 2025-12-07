<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}
$role = $_SESSION['role'] ?? 'viewer';
$current = basename($_SERVER['PHP_SELF']);

// --- KONEKSI DATABASE ---
$conn = mysqli_connect("localhost", "root", "", "sistem_pemantauan_udara");
if (!$conn) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

// --- AMBIL DATA UNTUK GRAFIK (30 DATA TERBARU) ---
$sql = "SELECT measured_at, suhu, kelembaban, pm25_level 
        FROM air_quality_data 
        ORDER BY measured_at DESC 
        LIMIT 30";
$result = mysqli_query($conn, $sql);

$timestamps = [];
$temperature = [];
$humidity = [];
$pm25 = [];

if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $timestamps[] = date('H:i', strtotime($row['measured_at']));
    $temperature[] = (float)$row['suhu'];
    $humidity[] = (float)$row['kelembaban'];
    $pm25[] = (float)$row['pm25_level'];
  }

  // Balik urutan agar data lama di kiri (waktu berurutan)
  $timestamps = array_reverse($timestamps);
  $temperature = array_reverse($temperature);
  $humidity = array_reverse($humidity);
  $pm25 = array_reverse($pm25);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Grafik Pengukuran Udara</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {
  font-family: 'Segoe UI';
  background: #f8f9fc;
  margin: 0;
}
header {
  background: #0046FF;
  padding: 15px 25px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
h1 {
  color: white;
}
nav a {
  margin-left: 15px;
  text-decoration: none;
  color: black;
  font-weight: 500;
  transition: color 0.3s ease;
}
nav a:hover {
  color: red;
}
nav a.active {
  color: red;
}
nav a.logout {
  font-weight: bold;
  color: black;
}
nav a.logout:hover {
  color: red;
}
.container {
  padding: 25px;
}
.chart {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
</style>
</head>
<body>
<header>
  <h1>GRAFIK PENGUKURAN</h1>
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
  <div class="chart">
    <h3>Grafik Suhu, Kelembaban, dan PM2.5</h3>
    <canvas id="chartUdara"></canvas>
  </div>
</div>

<script>
const labels = <?= json_encode($timestamps) ?>;
const suhuData = <?= json_encode($temperature) ?>;
const kelembabanData = <?= json_encode($humidity) ?>;
const pm25Data = <?= json_encode($pm25) ?>;

new Chart(document.getElementById('chartUdara'), {
  type: 'line',
  data: {
    labels: labels,
    datasets: [
      {
        label: 'Suhu (°C)',
        data: suhuData,
        borderColor: '#7b7fff',
        tension: 0.3,
        fill: false
      },
      {
        label: 'Kelembaban (%)',
        data: kelembabanData,
        borderColor: '#FFD700',
        tension: 0.3,
        fill: false
      },
      {
        label: 'PM2.5 (µg/m³)',
        data: pm25Data,
        borderColor: '#ff6384',
        tension: 0.3,
        fill: false
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: true,
        position: 'top'
      }
    },
    scales: {
      x: { title: { display: true, text: 'Waktu (Jam)' } },
      y: { beginAtZero: true, title: { display: true, text: 'Nilai Sensor' } }
    }
  }
});
</script>

</body>
</html>
