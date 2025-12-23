<?php
require_once 'includes/db_connection.php'; 

// Sadece yayınlanmış eserleri çekiyoruz
$artworks = [];
$sql = "SELECT a.title, a.description, a.image_path, c.name AS category_name
        FROM artworks a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.is_published = TRUE
        ORDER BY a.created_at DESC";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $artworks[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_TITLE; ?> | Galeri</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet"> 
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .header-content {
            min-height: 400px;
            background: #1a1a1a;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .card-img-top {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        .navbar { background-color: rgba(0,0,0,0.9) !important; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><?php echo SITE_TITLE; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Galeri</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">Hakkında</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">İletişim</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning ms-lg-3 btn-sm" href="admin/index.php">Yönetici Girişi</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<header class="header-content mt-5">
    <div class="container">
        <h1 class="display-4">Sanatın Dijital Dünyası</h1>
        <p class="lead">Eserlerimi keşfetmek için galeriye göz atın.</p>
    </div>
</header>

<section class="py-5" id="galeri">
    <div class="container">
        <h2 class="text-center mb-5">Eserler</h2>
        
        <?php if (empty($artworks)): ?>
            <div class="alert alert-info text-center">
                Henüz yayınlanmış bir eser bulunmamaktadır.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($artworks as $artwork): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo htmlspecialchars($artwork['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($artwork['title']); ?>">
                            <div class="card-body">
                                <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($artwork['category_name'] ?: 'Genel'); ?></span>
                                <h5 class="card-title mt-2"><?php echo htmlspecialchars($artwork['title']); ?></h5>
                                <p class="card-text text-muted small">
                                    <?php echo nl2br(htmlspecialchars(substr($artwork['description'], 0, 100))); ?>...
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <a href="#" class="btn btn-sm btn-outline-primary">İncele</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p><?php echo FOOTER_TEXT; ?></p>
        <p class="small text-muted">PHP & MySQL Portfolyo Sistemi</p>
    </div>
</footer>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>