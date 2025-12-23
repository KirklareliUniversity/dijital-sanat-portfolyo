<?php
session_start();
require_once('../includes/db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['site_title']);
    $desc = mysqli_real_escape_string($conn, $_POST['site_description']);
    $email = mysqli_real_escape_string($conn, $_POST['contact_email']);
    $footer = mysqli_real_escape_string($conn, $_POST['footer_text']);
    $about = mysqli_real_escape_string($conn, $_POST['about_text']);

    $update_query = "UPDATE settings SET 
                    site_title = '$title', 
                    site_description = '$desc', 
                    contact_email = '$email', 
                    footer_text = '$footer',
                    about_text = '$about' 
                    WHERE id = 1";

    if (mysqli_query($conn, $update_query)) {
        $message = "<div class='alert alert-success'>✅ Ayarlar ve bölümler güncellendi.</div>";
    }
}

$query = "SELECT * FROM settings WHERE id = 1";
$result = mysqli_query($conn, $query);
$settings = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Site Ayarları | Yönetim</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        body { background-color: #121212; color: white; }
        .card { background-color: #1e1e1e; border: none; }
        .form-control { background-color: #2b2b2b; color: white; border: 1px solid #444; }
        .form-control:focus { background-color: #333; color: white; border-color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between mb-4">
            <h2>⚙️ Site ve İçerik Ayarları</h2>
            <a href="index.php" class="btn btn-outline-warning">Geri Dön</a>
        </div>
        <?php echo $message; ?>
        <div class="card p-4 shadow">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-warning">Site Başlığı</label>
                        <input type="text" name="site_title" class="form-control" value="<?php echo htmlspecialchars($settings['site_title']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-warning">İletişim E-postası</label>
                        <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email']); ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-warning">Hakkında Yazısı (Ziyaretçi Sayfasında Görünür)</label>
                    <textarea name="about_text" class="form-control" rows="5"><?php echo htmlspecialchars($settings['about_text'] ?? ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="text-warning">Footer (Alt Bilgi)</label>
                    <input type="text" name="footer_text" class="form-control" value="<?php echo htmlspecialchars($settings['footer_text']); ?>">
                </div>
                <button type="submit" class="btn btn-warning w-100 mt-3">Tüm Değişiklikleri Kaydet</button>
            </form>
        </div>
    </div>
</body>
</html>