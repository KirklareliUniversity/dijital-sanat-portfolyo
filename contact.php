<?php require_once 'includes/db_connection.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İletişim | <?php echo SITE_TITLE; ?></title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background:#121212; color:white; padding-top:100px; }</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
    <div class="container"><a class="navbar-brand" href="index.php"><?php echo SITE_TITLE; ?></a>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Galeri</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">Hakkında</a></li>
        <li class="nav-item"><a class="nav-link active" href="contact.php">İletişim</a></li>
    </ul></div>
</nav>
<div class="container text-center">
    <h2>İletişim</h2>
    <p class="mt-4">Bana buradan ulaşabilirsiniz:</p>
    <h3 class="text-warning"><?php echo htmlspecialchars($site_data['contact_email']); ?></h3>
</div>
</body>
</html>