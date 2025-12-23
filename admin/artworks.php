<?php

session_start();

// db_connection.php'yi dahil etmeyi unutmayın

require_once '../includes/db_connection.php';



// Oturum Kontrolü

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {

    header('Location: login.php');

    exit;

}



// Tüm eserleri ve ilgili kategori adlarını çekme

$sql = "SELECT a.id, a.title, a.image_path, a.is_published, c.name AS category_name, a.created_at

        FROM artworks a

        LEFT JOIN categories c ON a.category_id = c.id

        ORDER BY a.created_at DESC";



$result = $conn->query($sql);

$artworks = [];

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $artworks[] = $row;

    }

}



// Bağlantıyı kapat (opsiyonel)

// $conn->close();

?>

<!DOCTYPE html>

<html lang="tr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Eser Yönetimi | Yönetici Paneli</title>

    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">

    <style>

        /* Basit sidebar stili */

        .sidebar { height: 100vh; background-color: #343a40; color: white; padding-top: 20px; position: fixed; }

        .sidebar a { color: #adb5bd; padding: 10px 15px; text-decoration: none; display: block; }

        .sidebar a:hover { background-color: #495057; color: white; }

        .artwork-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }

    </style>

</head>

<body>



<div class="container-fluid">

    <div class="row">

        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">

            <div class="position-sticky">

                <h4 class="text-white text-center mb-4 pt-3">Yönetim</h4>

                <ul class="nav flex-column">

                    <li class="nav-item"><a class="nav-link text-white" href="index.php">Dashboard</a></li>

                    <li class="nav-item"><a class="nav-link text-warning active" href="artworks.php">Eserler ve Sergiler</a></li>

                    <li class="nav-item"><a class="nav-link text-white" href="categories.php">Kategori Yönetimi</a></li>

                    <li class="nav-item"><a class="nav-link text-white" href="settings.php">Site Ayarları</a></li>

                </ul>

                <hr class="text-white">

                <ul class="nav flex-column">

                    <li class="nav-item">

                        <a class="nav-link text-white" href="logout.php">Çıkış Yap</a>

                    </li>

                </ul>

            </div>

        </nav>



        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">

                <h1 class="h2">Eser Yönetimi</h1>

                <a href="artworks_add.php" class="btn btn-success">Yeni Eser Ekle</a>

            </div>



            <?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>

                <div class="alert alert-warning" role="alert">Eser başarıyla silindi ve medya dosyası kaldırıldı.</div>

            <?php endif; ?>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>

                <div class="alert alert-danger" role="alert">HATA: Silme işlemi başarısız oldu.</div>

            <?php endif; ?>



            <?php if (empty($artworks)): ?>

                <div class="alert alert-info" role="alert">Henüz eklenmiş bir eser bulunmamaktadır.</div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-striped table-sm">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Görsel</th>

                                <th>Başlık</th>

                                <th>Kategori</th>

                                <th>Yayınlandı mı?</th>

                                <th>Tarih</th>

                                <th>İşlemler</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($artworks as $artwork): ?>

                                <tr>

                                    <td><?php echo htmlspecialchars($artwork['id']); ?></td>

                                    <td>

                                        <?php

                                            // Dosya yolu '/uploads/filename.ext' şeklinde olmalı

                                            // image_path, db_connection.php'den gelen yolu kullanır

                                            $image_url = str_replace('../uploads/', '../uploads/', htmlspecialchars($artwork['image_path']));

                                           

                                            // Sadece görsel dosyaları için önizleme yap

                                            $file_extension = strtolower(pathinfo($image_url, PATHINFO_EXTENSION));

                                            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])):

                                        ?>

                                                <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($artwork['title']); ?>" class="artwork-thumb">

                                        <?php else: ?>

                                                <span class="text-muted">Dosya</span>

                                        <?php endif; ?>

                                    </td>

                                    <td><?php echo htmlspecialchars($artwork['title']); ?></td>

                                    <td><?php echo htmlspecialchars($artwork['category_name'] ?: 'Tanımsız'); ?></td>

                                    <td>

                                        <span class="badge bg-<?php echo $artwork['is_published'] ? 'success' : 'danger'; ?>">

                                            <?php echo $artwork['is_published'] ? 'Evet' : 'Hayır'; ?>

                                        </span>

                                    </td>

                                    <td><?php echo date('d.m.Y', strtotime($artwork['created_at'])); ?></td>

                                    <td>
                                         <a href="artworks_edit.php?id=<?php echo $artwork['id']; ?>" class="btn btn-sm btn-info text-white">Düzenle</a>
    
                                         <a href="artworks_delete.php?id=<?php echo $artwork['id']; ?>" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Bu eseri silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');">Sil</a>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>



        </main>

    </div>

</div>



<script src="../assets/js/bootstrap.bundle.min.js"></script