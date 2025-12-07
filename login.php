<?php
session_start();
include 'koneksi.php';

$error = "";

if (isset($_POST['login'])) {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  // cek berdasarkan kolom 'name' dan 'password'
  $query = "SELECT * FROM users WHERE name='$username' AND password='$password'";
  $result = mysqli_query($conn, $query);

  if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $_SESSION['user'] = $row['name'];
    $_SESSION['role'] = $row['role'];
    header("Location: website.php");
    exit;
  } else {
    $error = "Username atau password salah!";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login - Monitoring Udara</title>
<style>
body {
  font-family: 'Segoe UI';
  background: #0046FF;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  margin: 0;
}
.container {
  background: #fff7c2;
  padding: 40px 50px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  width: 340px;
  text-align: center;
}
h2 {
  margin-bottom: 20px;
  color: #0047b3;
}
input[type="text"],
input[type="password"] {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
}
button {
  background: #ffe066;
  border: none;
  padding: 10px 20px;
  color: #000;
  font-weight: bold;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.3s;
}
button:hover {
  background: #ffda33;
}
.error {
  color: red;
  font-size: 14px;
  margin-top: 10px;
}
footer {
  margin-top: 20px;
  font-size: 13px;
  color: #555;
}
</style>
</head>
<body>
<div class="container">
  <h2>LOGIN SISTEM</h2>
  <form method="post">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="login">Masuk</button>
  </form>
  <?php if($error): ?>
    <div class="error"><?= $error ?></div>
  <?php endif; ?>
  <footer>Sistem Monitoring Udara</footer>
</div>
</body>
</html>
