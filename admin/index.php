<?php
session_start();
require_once '../includes/db_connection.php'; 

// Oturum Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$admin_username = $_SESSION['username']; 
$total_artworks = 0;

// Toplam Eser Sayısını Çekme
$sql = "SELECT COUNT(id) AS total FROM artworks";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total_artworks = $row['total'];
}

// Bağlantıyı kapat
$conn->close();
?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli | Dijital Portfolyo</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet"> 
    <link href="../assets/css/style.css" rel="stylesheet"> <style>
        /* Basit bir sidebar stili */
        .sidebar {
            height: 100vh;
            background-color: #343a40; /* Koyu gri */
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            color: #adb5bd;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
            color: white;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">
            <div class="position-sticky">
                <h4 class="text-white text-center mb-4">Yönetim</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="artworks.php">
                            Eserler ve Sergiler
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            Kategori Yönetimi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            Site Ayarları
                        </a>
                    </li>
                </ul>
                <hr class="text-white">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            Çıkış Yap (<?php echo htmlspecialchars($admin_username); ?>)
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Dashboard</h1>
            </div>

            <div class="alert alert-success" role="alert">
                Hoş geldiniz, **<?php echo htmlspecialchars($admin_username); ?>**! Yönetim paneline başarıyla giriş yaptınız.
            </div>

            <div class="row">
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Toplam Eser</h5>
                <p class="card-text fs-3"><?php echo $total_artworks; ?></p> 
            </div>
        </div>
    </div>
    </div>

        </main>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>