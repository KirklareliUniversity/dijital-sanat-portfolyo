<?php
session_start();
require_once '../includes/db_connection.php'; 

// Güvenlik: Admin değilse içeri alma
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = "";

// --- SADECE EKLEME İŞLEMİ ---
if (isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    if (!empty($name)) {
        $sql = "INSERT INTO categories (name) VALUES ('$name')";
        if ($conn->query($sql)) {
            $message = "<div class='alert alert-success'>✅ Yeni kategori başarıyla eklendi.</div>";
        }
    }
}

// --- SADECE SİLME İŞLEMİ ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE id = $id");
    header("Location: categories.php?status=deleted");
    exit;
}

if(isset($_GET['status']) && $_GET['status'] == 'deleted') {
    $message = "<div class='alert alert-danger'>🗑️ Kategori başarıyla silindi.</div>";
}

// Kategorileri Listele
$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategori Yönetimi</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: white; }
        .card { background-color: #1e1e1e; border: 1px solid #444; }
        .table { color: white; }
        .btn-warning { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📁 Kategori Listesi</h2>
            <a href="index.php" class="btn btn-outline-light">Geri Dön</a>
        </div>

        <?php echo $message; ?>

        <div class="row">
            <div class="col-md-5">
                <div class="card p-4 shadow">
                    <h5 class="text-warning mb-3">Yeni Kategori Ekle</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <input type="text" name="category_name" class="form-control bg-dark text-white border-secondary" placeholder="Kategori ismi yazın..." required>
                        </div>
                        <button type="submit" name="add_category" class="btn btn-warning w-100">Kategoriyi Kaydet</button>
                    </form>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card p-3 shadow">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Kategori Adı</th>
                                <th class="text-end">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $categories->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td class="text-end">
                                    <a href="categories.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')">
                                       Sil
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($categories->num_rows == 0): ?>
                                <tr><td colspan="2" class="text-center">Henüz kategori yok.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>