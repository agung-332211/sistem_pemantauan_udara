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

// --- AMBIL DATA SENSOR TERBARU ---
$sql = "SELECT measured_at, pm25_level 
        FROM air_quality_data 
        ORDER BY measured_at DESC 
        LIMIT 10";

$result = mysqli_query($conn, $sql);
$alerts = [];

if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $pm25 = (float)$row['pm25_level'];
    $waktu = $row['measured_at'];
    $lokasi = "Siskom 1";

    if ($pm25 <= 50) {
      $type = "good";
      $message = "✅ Normal. Udara di <b>$lokasi</b> dalam kondisi baik ({$pm25} µg/m³).";
    } elseif ($pm25 <= 100) {
      $type = "warning";
      $message = "⚠️ Peringatan! Kadar PM2.5 meningkat di <b>$lokasi</b> ({$pm25} µg/m³).";
    } elseif ($pm25 <= 200) {
      $type = "";
      $message = "⚠️ Peringatan! Udara di <b>$lokasi</b> tidak sehat ({$pm25} µg/m³).";
    } else {
      $type = "";
      $message = "🚨 Bahaya! Kualitas udara di <b>$lokasi</b> sangat berbahaya ({$pm25} µg/m³).";
    }

    $alerts[] = [
      'type' => $type,
      'message' => $message,
      'waktu' => $waktu
    ];
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Alert - Monitoring Udara</title>
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
.alert {
  background: white;
  padding: 20px;
  margin-bottom: 10px;
  border-left: 6px solid red;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}
.alert.warning {
  border-left-color: orange;
}
.alert.good {
  border-left-color: green;
}
.time {
  font-size: 0.85em;
  color: #555;
  margin-top: 5px;
}
</style>
</head>
<body>
<header>
  <h1>ALERT SISTEM</h1>
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
  <?php if (empty($alerts)): ?>
    <div class="alert good">
      <strong>✅ Normal.</strong> Belum ada data sensor atau semua sensor dalam kondisi aman.
    </div>
  <?php else: ?>
    <?php foreach ($alerts as $a): ?>
      <div class="alert <?= $a['type'] ?>">
        <strong><?= $a['message'] ?></strong>
        <div class="time">⏱️ <?= $a['waktu'] ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</body>
</html>
