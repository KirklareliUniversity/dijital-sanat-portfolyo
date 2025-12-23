<?php
// Hata gösterimini aç
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

session_start();
// Veritabanı bağlantısını dahil et
require_once '../includes/db_connection.php'; 



// Eğer kullanıcı zaten giriş yapmışsa ve admin rolündeyse ana sayfaya yönlendir
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: index.php');
    exit;
}

$error_message = ''; // Hata mesajlarını tutacak değişken

// Form Gönderimini İşleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Güvenlik: Kullanıcıdan gelen verileri temizle
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password']; 

    // Veritabanından Kullanıcıyı Çek
    // Sadece 'admin' rolüne sahip kullanıcıları kontrol ediyoruz
    $sql = "SELECT id, password, role FROM users WHERE username = ? AND role = 'admin'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Kullanıcı bulundu
        $user = $result->fetch_assoc();
        $hashed_password_from_db = $user['password'];

        // Şifre Doğrulama (GÜVENLİ ÇÖZÜM)
        if (password_verify($password, $hashed_password_from_db)) {
            
            // Giriş Başarılı! Oturumu (Session) başlat
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];
            
            // Admin paneline yönlendir
            header('Location: index.php'); 
            exit;

        } else {
            // Şifre yanlış
            $error_message = 'Kullanıcı adı veya şifre yanlış.';
        }
    } else {
        // Kullanıcı adı bulunamadı
        $error_message = 'Kullanıcı adı veya şifre yanlış.';
    }

    $stmt->close();
}

// $conn->close(); // Bağlantıyı kapatmak (isteğe bağlı, sayfa sonunda otomatik kapanabilir)
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Girişi | Dijital Portfolyo</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet"> 
    <style>
        /* Stil kısmı aynı kalır */
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin-top: 100px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            background-color: #fff;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 login-container">
                <h3 class="text-center mb-4">Yönetici Girişi</h3>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Giriş Yap</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>