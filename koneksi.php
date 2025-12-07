<?php
$conn = mysqli_connect("localhost", "root", "", "sistem_pemantauan_udara");
if (!$conn) {
  die("Koneksi gagal: " . mysqli_connect_error());
}
?>
