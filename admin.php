<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
  header("Location: login.php");
  exit;
}

$conn = mysqli_connect("localhost", "root", "", "sistem_pemantauan_udara");
if (!$conn) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

// --- TAMBAH DATA (password disimpan PLAIN TEXT sesuai permintaan) ---
if (isset($_POST['add'])) {
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  // langsung simpan password apa adanya (TIDAK DI-HASH)
  $password_plain = mysqli_real_escape_string($conn, $_POST['password']);
  $role = mysqli_real_escape_string($conn, $_POST['role']);

  $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password_plain', '$role')";
  mysqli_query($conn, $sql);
}

// --- UPDATE DATA (tidak mengubah password lewat update ini) ---
if (isset($_POST['update'])) {
  $id = (int)$_POST['id'];
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $role = mysqli_real_escape_string($conn, $_POST['role']);
  mysqli_query($conn, "UPDATE users SET name='$name', email='$email', role='$role' WHERE id=$id");
}

// --- HAPUS DATA ---
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  mysqli_query($conn, "DELETE FROM users WHERE id=$id");
}

// --- AMBIL SEMUA DATA PENGGUNA ---
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin Panel</title>
<style>
body{font-family:'Segoe UI';background:#f8f9fc;margin:0;}
header{background:#0046FF;padding:15px 25px;display:flex;justify-content:space-between;align-items:center;}
h1{color:white;}
nav a{margin-left:15px;text-decoration:none;color:black;font-weight:500;transition:color 0.3s;}
nav a:hover{color:red;}
nav a.active{color:red;}
nav a.logout{font-weight:bold;}
nav a.logout:hover{color:red;}
.container{padding:25px;}
table{width:90%;margin:auto;border-collapse:collapse;background:white;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
th,td{padding:8px;text-align:center;border-bottom:1px solid #eee;}
th{background:#e1e5ff;}
form{margin:20px auto;width:90%;background:white;padding:15px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
input,select{padding:6px;margin:4px;}
button{padding:6px 12px;border:none;border-radius:5px;background:#0046FF;color:white;cursor:pointer;}
button:hover{background:red;}
.edit-form{display:inline;}
</style>
</head>
<body>
<header>
  <h1>ADMIN PANEL</h1>
  <?php $current = basename($_SERVER['PHP_SELF']); ?>
  <nav>
    <a href="website.php" class="<?= $current=='website.php'?'active':'' ?>">Dashboard</a>
    <a href="sensor.php" class="<?= $current=='sensor.php'?'active':'' ?>">Sensor</a>
    <a href="grafik.php" class="<?= $current=='grafik.php'?'active':'' ?>">Grafik</a>
    <a href="alert.php" class="<?= $current=='alert.php'?'active':'' ?>">Alerts</a>
    <a href="admin.php" class="<?= $current=='admin.php'?'active':'' ?>">Admin</a>
    <a href="logout.php" class="logout">Logout</a>
  </nav>
</header>

<div class="container">
  <h3>Tambah Pengguna Baru</h3>
  <form method="post">
    <input type="text" name="name" placeholder="Nama" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <select name="role">
      <option value="viewer">Viewer</option>
      <option value="admin">Admin</option>
    </select>
    <button type="submit" name="add">Tambah</button>
  </form>

  <h3>Daftar Pengguna</h3>
  <table>
    <tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['role']) ?></td>
        <td>
          <form method="post" class="edit-form">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>
            <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
            <select name="role">
              <option value="viewer" <?= $row['role']=='viewer'?'selected':'' ?>>Viewer</option>
              <option value="admin" <?= $row['role']=='admin'?'selected':'' ?>>Admin</option>
            </select>
            <button type="submit" name="update">Simpan</button>
          </form>
          <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus pengguna ini?')" style="color:red;">Hapus</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
</body>
</html>
