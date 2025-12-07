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

// --- AMBIL DATA DARI DATABASE ---
$sql = "SELECT measured_at, suhu, kelembaban, pm25_level 
        FROM air_quality_data 
        ORDER BY measured_at DESC";
$result = mysqli_query($conn, $sql);
$dataSensor = [];

if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $pm25 = $row['pm25_level'];
    // Tentukan status otomatis berdasarkan nilai PM2.5
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
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Sensor</title>
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
table{
  width:90%;
  margin:30px auto;
  border-collapse:collapse;
  background:white;
  box-shadow:0 2px 6px rgba(0,0,0,0.1);
}
th,td{
  border:1px solid #eee;
  padding:8px;
  text-align:center;
}
th{
  background:#87BAC3;
}
</style>
</head>
<body>
<header>
  <h1>DATA SENSOR</h1>
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

<table>
  <tr>
    <th>Waktu</th>
    <th>Lokasi</th>
    <th>Suhu (°C)</th>
    <th>Kelembaban (%)</th>
    <th>PM2.5 (µg/m³)</th>
    <th>Status</th>
  </tr>

  <?php if (empty($dataSensor)): ?>
    <tr><td colspan="6">Belum ada data sensor</td></tr>
  <?php else: ?>
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
  <?php endif; ?>
</table>
</body>
</html>
