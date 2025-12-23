<?php
session_start();
require_once '../includes/db_connection.php'; 

// Oturum Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$artwork_id = (int)$_GET['id'] ?? 0; 
$error_message = '';
$success_message = '';
$artwork = null;
$categories = [];

// Eser ID kontrolü
if ($artwork_id === 0) {
    header('Location: artworks.php'); 
    exit;
}

// ------------------------------------
// KATEGORİLERİ ÇEKME
// ------------------------------------
$cat_sql = "SELECT id, name FROM categories ORDER BY name ASC";
$cat_result = $conn->query($cat_sql);
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// ------------------------------------
// MEVCUT ESERİ VERİTABANINDAN ÇEKME (FORM İÇİN)
// ------------------------------------
$sql = "SELECT * FROM artworks WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $artwork_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $artwork = $result->fetch_assoc();
} else {
    // Eser bulunamazsa listeye yönlendir
    header('Location: artworks.php');
    exit;
}
$stmt->close();


// ------------------------------------
// FORM GÖNDERİMİNİ İŞLEME (GÜNCELLEME)
// ------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Güvenlik: POST verilerini al ve temizle
    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    $category_id = (int)$_POST['category_id']; // YENİ
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    // Güncelleme sorgusu için başlangıç
    // category_id eklendi
    $update_fields = "title = ?, description = ?, category_id = ?, is_published = ?";
    $params = [$title, $description, $category_id, $is_published];
    $param_types = 'ssii';
    $old_file_path = $artwork['image_path']; 
    $file_uploaded = false;
    
    // 1. Dosya Yükleme Kontrolleri (Yeni dosya seçilmişse)
    if (isset($_FILES['artwork_file']) && $_FILES['artwork_file']['error'] === UPLOAD_ERR_OK) {
        
        $target_dir = "../uploads/";
        $file_tmp = $_FILES['artwork_file']['tmp_name'];
        $file_name = $_FILES['artwork_file']['name'];
        $file_size = $_FILES['artwork_file']['size'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'pdf'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_extensions)) {
            $error_message = 'Yalnızca resim, video (mp4) ve PDF dosyaları yüklenebilir.';
        } elseif ($file_size > 5 * 1024 * 1024) { 
            $error_message = 'Dosya boyutu 5 MB\'ı geçemez.';
        } else {
            // Güvenli dosya adı oluştur
            $new_file_name = uniqid('artwork_', true) . '.' . $file_ext;
            $upload_path = $target_dir . $new_file_name;

            // Dosyayı sunucuya taşı
            if (move_uploaded_file($file_tmp, $upload_path)) {
                
                // DB'ye kaydedilecek yolu hazırlama (uploads/dosya.jpg)
                $db_image_path = "uploads/" . $new_file_name; 

                // Veritabanı alanlarını ve parametrelerini güncelle
                $update_fields .= ", image_path = ?";
                $params[] = $db_image_path; 
                $param_types .= 's';
                $file_uploaded = true;
            } else {
                $error_message = 'Dosya yükleme sırasında bir hata oluştu.';
            }
        }
    }

    // 2. Veritabanı Güncelleme
    if (!$error_message) {
        // ID'yi parametre listesinin sonuna ekle
        $params[] = $artwork_id;
        $param_types .= 'i';

        $update_sql = "UPDATE artworks SET {$update_fields} WHERE id = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        // bind_param için call_user_func_array gerekliydi, ama PHP 5.6+ ile ...$params kullanıyoruz
        $update_stmt->bind_param($param_types, ...$params); 

        if ($update_stmt->execute()) {
            
            // Eğer yeni dosya yüklendiyse, eski dosyayı sil
            if ($file_uploaded && !empty($old_file_path)) {
                 $full_old_path = "../" . $old_file_path; // Eski yolu oluştur
                if (file_exists($full_old_path)) {
                    @unlink($full_old_path); // Hata vermemesi için @ kullanıldı
                }
            }

            // Başarılı yönlendirme
            header("Location: artworks.php?status=updated");
            exit;

        } else {
            $error_message = 'Eser güncellenirken bir hata oluştu: ' . $update_stmt->error;
        }
        $update_stmt->close();
    }
}

// Güncel veriyi almak için eseri tekrar çek
$sql = "SELECT * FROM artworks WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $artwork_id);
$stmt->execute();
$artwork = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close(); 
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eser Düzenle | Yönetici Paneli</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet"> 
    <style>
        .sidebar { height: 100vh; background-color: #343a40; color: white; padding-top: 20px; position: fixed; }
        .sidebar a { color: #adb5bd; padding: 10px 15px; text-decoration: none; display: block; }
        .sidebar a:hover { background-color: #495057; color: white; }
        .current-media { max-width: 200px; height: auto; border: 1px solid #ccc; padding: 5px; }
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
                <h1 class="h2">Eser Düzenle: <?php echo htmlspecialchars($artwork['title']); ?></h1>
                <a href="artworks.php" class="btn btn-secondary">Eser Listesine Dön</a>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success" role="alert"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">Eser Detayları</div>
                <div class="card-body">
                    <form action="artworks_edit.php?id=<?php echo $artwork_id; ?>" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Eser Başlığı</label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   value="<?php echo htmlspecialchars($artwork['title']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Kategori</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Kategori Seçiniz</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"
                                        <?php echo ($artwork['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
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
                            <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($artwork['description']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="is_published" name="is_published"
                                       <?php echo $artwork['is_published'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_published">
                                    Yayında (Ziyaretçiler görebilir)
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mevcut Medya</label><br>
                            <?php 
                                $image_url = BASE_URL . "/" . htmlspecialchars($artwork['image_path']);
                                $file_ext = strtolower(pathinfo($image_url, PATHINFO_EXTENSION));

                                if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])):
                            ?>
                                <img src="<?php echo $image_url; ?>" alt="Mevcut Görsel" class="current-media img-fluid">
                            <?php else: ?>
                                <p class="text-muted">Dosya: <?php echo basename($artwork['image_path']); ?></p>
                            <?php endif; ?>
                            <small class="d-block mt-2">Mevcut dosya yolu: <?php echo htmlspecialchars($artwork['image_path']); ?></small>
                        </div>


                        <div class="mb-3">
                            <label for="artwork_file" class="form-label">Yeni Dosya Yükle (Değiştirmek isterseniz)</label>
                            <input class="form-control" type="file" id="artwork_file" name="artwork_file">
                            <div class="form-text">Yeni bir dosya yüklerseniz, mevcut dosyanın yerini alacaktır.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>


