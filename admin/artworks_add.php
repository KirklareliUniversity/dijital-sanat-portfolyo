<?php
session_start();
require_once '../includes/db_connection.php'; 

// Oturum Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id']; 
$error_message = '';
$categories = []; // Kategoriler dizisi

// --- KATEGORİLERİ VERİTABANINDAN ÇEKME ---
$category_sql = "SELECT id, name FROM categories ORDER BY name ASC";
$category_result = $conn->query($category_sql);
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}
// ------------------------------------------


// --- FORM GÖNDERİMİNİ İŞLEME ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = (int)$_POST['category_id']; // YENİ: Kategori ID'si
    $uploadOk = 1;
    $target_file = null;
    $db_image_path = null;

    // Alan Kontrolleri
    if (empty($title) || empty($description) || $category_id === 0) {
        $error_message = 'Lütfen tüm alanları doldurun ve geçerli bir kategori seçin.';
        $uploadOk = 0;
    }
    
    // Dosya Yükleme İşlemi
    if ($uploadOk == 1 && isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        
        $target_dir = "../uploads/";
        $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $unique_name = uniqid('artwork_') . '.' . $imageFileType;
        $target_file = $target_dir . $unique_name;

        // Dosya boyutu ve formatı kontrolü
        if ($_FILES['image']['size'] > 5000000) { // 5MB
            $error_message = "Üzgünüm, dosyanız çok büyük (Max 5MB).";
            $uploadOk = 0;
        }

        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $error_message = "Üzgünüm, sadece JPG, JPEG, PNG & GIF dosyalarına izin verilir.";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
             if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $db_image_path = "uploads/" . $unique_name; // DB'ye kaydedilecek yol
            } else {
                $error_message = "Üzgünüm, dosyanız yüklenirken bir hata oluştu. Sunucu izinlerini kontrol edin.";
                $uploadOk = 0;
            }
        }
    } else {
        $error_message = "Lütfen yüklenecek bir eser görseli seçin.";
        $uploadOk = 0;
    }


    // Veritabanına Kayıt İşlemi
    if ($uploadOk == 1) {
        // user_id, title, description, category_id, image_path
        $insert_sql = "INSERT INTO artworks (user_id, title, description, category_id, image_path) VALUES (?, ?, ?, ?, ?)";
        
        $insert_stmt = $conn->prepare($insert_sql);
        // Parametre tipleri: i (user_id), s (title), s (description), i (category_id), s (image_path)
        $insert_stmt->bind_param("issis", $user_id, $title, $description, $category_id, $db_image_path);

        if ($insert_stmt->execute()) {
            // Başarı durumunda listeye yönlendir
            header('Location: artworks.php?status=added');
            exit;
        } else {
            $error_message = "Veritabanına kayıt sırasında hata oluştu: " . $conn->error;
            // Hata durumunda yüklenen dosyayı sil
            if ($target_file && file_exists($target_file)) {
                @unlink($target_file);
            }
        }
        $insert_stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Eser Ekle | Yönetici Paneli</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet"> 
    <style>
        .sidebar { height: 100vh; background-color: #343a40; color: white; padding-top: 20px; position: fixed; }
        .sidebar a { color: #adb5bd; padding: 10px 15px; text-decoration: none; display: block; }
        .sidebar a:hover { background-color: #495057; color: white; }
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
                    <li class="nav-item"><a class="nav-link text-white" href="logout.php">Çıkış Yap</a></li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Yeni Eser Ekle</h1>
                <a href="artworks.php" class="btn btn-secondary">Eser Listesine Dön</a>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">Eser Detayları</div>
                <div class="card-body">
                    <form action="artworks_add.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Eser Adı</label>
                            <input type="text" class="form-control" id="title" name="title" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="0">Kategori Seçin</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($categories)): ?>
                                <div class="form-text text-danger">⚠️ Lütfen önce <a href="categories.php">Kategori Yönetimi</a> sayfasından kategori ekleyin.</div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Eser Görseli (JPG, PNG vb.)</label>
                            <input class="form-control" type="file" id="image" name="image" accept="image/*" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Eseri Ekle</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>


