<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $suhu = $_POST['suhu'];
  $kelembaban = $_POST['kelembaban'];
  $pm25 = $_POST['pm25'];

  $query = "INSERT INTO air_quality_data (suhu, kelembaban, pm25_level) VALUES ('$suhu', '$kelembaban', '$pm25')";
  if (mysqli_query($conn, $query)) {
    echo "OK";
  } else {
    echo "ERROR: " . mysqli_error($conn);
  }
} else {
  echo "Gunakan metode POST";
}
?>
